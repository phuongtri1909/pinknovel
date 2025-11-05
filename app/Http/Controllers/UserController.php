<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use App\Models\Bookmark;
use App\Models\Banned_ip;
use App\Models\UserReading;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\OTPUpdateUserMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Intervention\Image\Facades\Image;
use App\Services\ReadingHistoryService;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function show($id, Request $request)
    {
        $authUser = auth()->user();
        $user = User::findOrFail($id);

        if ($authUser->role === 'mod') {
            if ($user->role === 'admin' || $user->role === 'mode') {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($user->active !== 'active') {
            abort(404);
        }

        $tabToPageNameMap = [
            'deposits' => 'deposits_page',
            'paypal-deposits' => 'paypal_deposits_page',
            'card-deposits' => 'card_deposits_page',
            'story-purchases' => 'story_page',
            'chapter-purchases' => 'chapter_page',
            'author-stories' => 'author_stories_page',
            'author-chapter-earnings' => 'author_chapter_earnings_page',
            'author-story-earnings' => 'author_story_earnings_page',
            'author-featured-stories' => 'author_featured_stories_page',
            'bookmarks' => 'bookmarks_page',
            'user-daily-tasks' => 'daily_tasks_page',
            'withdrawal-requests' => 'withdrawals_page',
            'coin-transactions' => 'coin_page',
            'coin-history' => 'coin_histories_page',
        ];

        $getPageName = function($tab) use ($tabToPageNameMap) {
            return $tabToPageNameMap[$tab] ?? null;
        };

        $stats = [
            'total_deposits' => $user->total_deposits,
            'total_spent' => $user->total_chapter_spending + $user->total_story_spending,
            'balance' => $user->coins,
            'total_withdrawn' => $user->withdrawalRequests()->where('status', 'approved')->sum('coins'),
            'author_revenue' => $user->role === 'author' ? $user->author_revenue : 0,
            'author_story_revenue' => $user->role === 'author' ? \App\Models\StoryPurchase::whereHas('story', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('amount_received') : 0,
            'author_chapter_revenue' => $user->role === 'author' ? \App\Models\ChapterPurchase::whereHas('chapter.story', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('amount_received') : 0,
        ];

        $getPageNumber = function($tab) use ($request, $getPageName) {
            $currentTab = $request->get('tab');
            if ($currentTab === $tab && $request->has('page')) {
                return $request->get('page', 1);
            }
            $pageName = $getPageName($tab);
            if ($pageName && $request->has($pageName)) {
                return $request->get($pageName, 1);
            }
            return 1;
        };

        $depositsPage = $getPageNumber('deposits');
        $paypalDepositsPage = $getPageNumber('paypal-deposits');
        $cardDepositsPage = $getPageNumber('card-deposits');
        $chapterPage = $getPageNumber('chapter-purchases');
        $storyPage = $getPageNumber('story-purchases');
        $bookmarksPage = $getPageNumber('bookmarks');
        $coinPage = $getPageNumber('coin-transactions');
        $dailyTasksPage = $getPageNumber('user-daily-tasks');
        $withdrawalsPage = $getPageNumber('withdrawal-requests');
        $authorStoriesPage = $getPageNumber('author-stories');
        $authorChapterEarningsPage = $getPageNumber('author-chapter-earnings');
        $authorStoryEarningsPage = $getPageNumber('author-story-earnings');
        $authorFeaturedStoriesPage = $getPageNumber('author-featured-stories');
        $coinHistoriesPage = $getPageNumber('coin-history');

        $deposits = $user->deposits()
            ->with('bank')
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'deposits_page', $depositsPage);

        $paypalDeposits = $user->paypalDeposits()
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'paypal_deposits_page', $paypalDepositsPage);

        $cardDeposits = $user->cardDeposits()
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'card_deposits_page', $cardDepositsPage);

        $chapterPurchases = $user->chapterPurchases()
            ->with(['chapter.story'])
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'chapter_page', $chapterPage);

        $storyPurchases = $user->storyPurchases()
            ->with(['story'])
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'story_page', $storyPage);

        $bookmarks = $user->bookmarks()
            ->with(['story', 'lastChapter'])
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'bookmarks_page', $bookmarksPage);

        $coinTransactions = $user->coinTransactions()
            ->with('admin')
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'coin_page', $coinPage);

        $userDailyTasks = $user->userDailyTasks()
            ->with('dailyTask')
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'daily_tasks_page', $dailyTasksPage);

        $withdrawalRequests = $user->withdrawalRequests()
            ->with('processedBy')
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'withdrawals_page', $withdrawalsPage);

        $authorChapterEarnings = collect();
        $authorStoryEarnings = collect();
        $authorFeaturedStories = collect();
        $authorStories = collect();
        
        if ($user->role === 'author') {
            $authorStories = \App\Models\Story::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->paginate(25, ['*'], 'author_stories_page', $authorStoriesPage);

            $authorChapterEarnings = \App\Models\ChapterPurchase::whereHas('chapter.story', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['chapter.story', 'user'])
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'author_chapter_earnings_page', $authorChapterEarningsPage);

            $authorStoryEarnings = \App\Models\StoryPurchase::whereHas('story', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['story', 'user'])
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'author_story_earnings_page', $authorStoryEarningsPage);

            $authorFeaturedStories = \App\Models\StoryFeatured::where('user_id', $user->id)
                ->where('type', \App\Models\StoryFeatured::TYPE_AUTHOR)
                ->with(['story'])
                ->orderByDesc('created_at')
                ->paginate(25, ['*'], 'author_featured_stories_page', $authorFeaturedStoriesPage);
        }

        $coinHistories = $user->coinHistories()
            ->orderByDesc('created_at')
            ->paginate(25, ['*'], 'coin_histories_page', $coinHistoriesPage);

        $counts = DB::select("
            SELECT 
                (SELECT COUNT(*) FROM deposits WHERE user_id = ?) as deposits,
                (SELECT COUNT(*) FROM paypal_deposits WHERE user_id = ?) as paypal_deposits,
                (SELECT COUNT(*) FROM card_deposits WHERE user_id = ?) as card_deposits,
                (SELECT COUNT(*) FROM chapter_purchases WHERE user_id = ?) as chapter_purchases,
                (SELECT COUNT(*) FROM story_purchases WHERE user_id = ?) as story_purchases,
                (SELECT COUNT(*) FROM bookmarks WHERE user_id = ?) as bookmarks,
                (SELECT COUNT(*) FROM coin_transactions WHERE user_id = ?) as coin_transactions,
                (SELECT COUNT(*) FROM user_daily_tasks WHERE user_id = ?) as user_daily_tasks,
                (SELECT COUNT(*) FROM withdrawal_requests WHERE user_id = ?) as withdrawal_requests,
                (SELECT COUNT(*) FROM coin_histories WHERE user_id = ?) as coin_histories,
                (SELECT COUNT(*) FROM story_featureds WHERE user_id = ? AND type = 'author') as author_featured_stories,
                (SELECT COUNT(*) FROM stories WHERE user_id = ?) as author_stories,
                (SELECT COUNT(*) FROM chapter_purchases cp 
                 JOIN chapters c ON cp.chapter_id = c.id 
                 JOIN stories s ON c.story_id = s.id 
                 WHERE s.user_id = ?) as author_chapter_earnings,
                (SELECT COUNT(*) FROM story_purchases sp 
                 JOIN stories s ON sp.story_id = s.id 
                 WHERE s.user_id = ?) as author_story_earnings
        ", [
            $user->id, $user->id, $user->id, $user->id, $user->id, 
            $user->id, $user->id, $user->id, $user->id, $user->id,
            $user->id, $user->id, $user->id, $user->id
        ])[0];

        $counts = (array) $counts;

        return view('admin.pages.users.show', compact(
            'user',
            'stats',
            'deposits',
            'paypalDeposits',
            'cardDeposits',
            'chapterPurchases',
            'storyPurchases',
            'bookmarks',
            'coinTransactions',
            'userDailyTasks',
            'withdrawalRequests',
            'authorChapterEarnings',
            'authorStoryEarnings',
            'authorFeaturedStories',
            'authorStories',
            'coinHistories',
            'counts'
        ));
    }

    public function update(Request $request, $id)
    {
        $authUser = auth()->user();
        $user = User::findOrFail($id);


        if ($request->has('delete_avatar') && $authUser->role === 'admin') {
            if (in_array($user->role, ['admin', 'mod'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể xóa ảnh đại diện của Admin/Mod'
                ], 403);
            }

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = null;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã xóa ảnh đại diện'
            ]);
        }

        $superAdminEmails = explode(',', env('SUPER_ADMIN_EMAILS', 'admin@gmail.com'));
        $isSuperAdmin = in_array($authUser->email, $superAdminEmails);

        if ($request->has('role')) {
            if (in_array($user->email, $superAdminEmails)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể thay đổi quyền của Super Admin'
                ], 403);
            }

            if ($user->role === 'admin' && !$isSuperAdmin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không có quyền thực hiện'
                ], 403);
            }

            $request->validate([
                'role' => 'required|in:user,admin,author'
            ], [
                'role.required' => 'Trường role không được để trống',
                'role.in' => 'Giá trị không hợp lệ'
            ]);

            $user->role = $request->role;
        }

        if ($authUser->role === 'mod') {
            if ($user->role === 'admin' || $user->id === $authUser->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không có quyền thực hiện'
                ], 403);
            }
        }

        $banTypes = ['login', 'comment', 'rate', 'read'];
        foreach ($banTypes as $type) {
            $field = "ban_$type";
            if ($request->has($field)) {
                $user->$field = $request->boolean($field);
            }
        }

        try {
            $user->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra'
            ], 500);
        }
    }

    public function banIp(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'ban' => 'required|in:true,false,0,1'
        ], [
            'ban.required' => 'Trường ban không được để trống',
            'ban.in' => 'Giá trị không hợp lệ'
        ]);

        if ($request->boolean('ban')) {
            if (!$user->ip_address) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy IP của người dùng'
                ], 400);
            }

            // Check if IP already banned
            if (!Banned_ip::where('ip_address', $user->ip_address)->exists()) {
                Banned_ip::create([
                    'ip_address' => $user->ip_address,
                    'user_id' => $user->id
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đã thêm IP vào danh sách cấm'
            ]);
        } else {
            // Remove all banned IPs for this user
            Banned_ip::where('user_id', $user->id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã xóa IP khỏi danh sách cấm'
            ]);
        }
    }

    public function index(Request $request)
    {
        $authUser = auth()->user();

        $query = User::query();

        $stats = [
            'total' => User::where('active', 'active')->count(),
            'admin' => User::where('active', 'active')->where('role', 'admin')->count(),
            'mod' => User::where('active', 'active')->where('role', 'mod')->count(),
            'user' => User::where('active', 'active')->where('role', 'user')->count(),
            'author' => User::where('active', 'active')->where('role', 'author')->count(),
        ];

        if ($authUser->role === 'mod') {
            $query->where('role', '!=', 'admin')->where('role', '!=', 'mod');
        }


        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // IP filter
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . $request->ip . '%');
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        $users = $query->where('active', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin.pages.users.index', compact('users', 'stats'));
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if ($request->has('otp')) {
            $otp = $request->otp;

            if (!password_verify($otp, $user->key_reset_password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => ['otp' => ['Mã OTP không chính xác để thay đổi mật khẩu']],
                ], 422);
            }

            if ($request->has('password') && $request->has('password_confirmation')) {
                try {
                    $request->validate([
                        'password' => 'required|min:6|confirmed',
                    ], [
                        'password.required' => 'Hãy nhập mật khẩu mới',
                        'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
                        'password.confirmed' => 'Mật khẩu xác nhận không khớp',
                    ]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->errors()
                    ], 422);
                }

                $user->password = bcrypt($request->password);
                $user->key_reset_password = null;

                $user->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Cập nhật mật khẩu thành công',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Xác thực OTP thành công',
            ], 200);
        }

        $otp = $this->generateRandomOTP();
        if ($user->reset_password_at != null) {
            $resetPasswordAt = Carbon::parse($user->reset_password_at);
            if (!$resetPasswordAt->lt(Carbon::now()->subMinutes(3))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP đã được gửi đến Email của bạn, hoặc thử lại sau 3 phút',
                ], 200);
            }
        }

        $user->key_reset_password = bcrypt($otp);
        $user->reset_password_at = now();
        $user->save();

        Mail::to($user->email)->send(new OTPUpdateUserMail($otp, 'password'));
        return response()->json([
            'status' => 'success',
            'message' => 'Gửi mã OTP thành công, vui lòng kiểm tra Email của bạn',
        ], 200);
    }

    public function updateBankAccount(Request $request)
    {
        $user = Auth::user();

        if ($request->has('otp')) {
            $otp = $request->otp;

            if (!password_verify($otp, $user->key_change_bank)) {
                return response()->json([
                    'status' => 'error',
                    'message' => ['otp' => ['Mã OTP không chính xác để thay đổi thông tin ngân hàng']],
                ], 422);
            }

            if ($request->has('bank_id') && $request->has('account_number') && $request->has('account_name')) {
                try {
                    $request->validate([
                        'bank_id' => 'required|exists:banks,id',
                        'account_number' => 'required',
                        'account_name' => 'required',
                    ], [
                        'bank_id.required' => 'Hãy chọn ngân hàng',
                        'bank_id.exists' => 'Ngân hàng không tồn tại',
                        'account_number.required' => 'Hãy nhập số tài khoản',
                        'account_name.required' => 'Hãy nhập tên chủ tài khoản',
                    ]);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->errors()
                    ], 422);
                }

                $user->key_change_bank = null;

                $data = [
                    'bank_id' => $request->input('bank_id'),
                    'account_number' => $request->input('account_number'),
                    'account_name' => $request->input('account_name'),
                ];

                $bank_account = $user->bankAccount()->updateOrCreate(
                    ['user_id' => $user->id], // Điều kiện để kiểm tra sự tồn tại
                    $data // Dữ liệu cần cập nhật hoặc tạo mới
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Cập nhật thông tin ngân hàng thành công',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Xác thực OTP thành công',
            ], 200);
        }

        $otp = $this->generateRandomOTP();
        if ($user->change_bank_at != null) {
            $changeBankAt = Carbon::parse($user->change_bank_at);
            if (!$changeBankAt->lt(Carbon::now()->subMinutes(3))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP đã được gửi đến Email của bạn, hoặc thử lại sau 3 phút',
                ], 200);
            }
        }

        $user->key_change_bank = bcrypt($otp);
        $user->change_bank_at = now();
        $user->save();

        Mail::to($user->email)->send(new OTPUpdateUserMail($otp, 'Banking'));

        return response()->json([
            'status' => 'success',
            'message' => 'Gửi mã OTP thành công, vui lòng kiểm tra Email của bạn',
        ], 200);
    }

    public function userProfile()
    {
        $user = Auth::user();

        return view('pages.information.profile', compact('user'));
    }

    public function bookmarks()
    {
        $user = Auth::user();
        $bookmarks = Bookmark::where('user_id', $user->id)
            ->with(['story' => function ($query) {
                $query->with('latestChapter');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.information.bookmarks', compact('bookmarks'));
    }


    public function toggleBookmark(Request $request)
    {
        $request->validate([
            'story_id' => 'required|exists:stories,id',
        ]);

        $result = Bookmark::toggleBookmark(Auth::id(), $request->story_id);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'bookmark_status' => $result['status'],
                'message' => $result['message']
            ]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    // Remove bookmark
    public function removeBookmark(Request $request)
    {
        $request->validate([
            'bookmark_id' => 'required|exists:bookmarks,id',
        ]);

        $bookmark = Bookmark::findOrFail($request->bookmark_id);

        // Check if the bookmark belongs to the current user
        if ($bookmark->user_id != Auth::id()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không có quyền thực hiện hành động này'
                ], 403);
            }
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện hành động này');
        }

        $bookmark->delete();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Đã xóa truyện khỏi danh sách theo dõi'
            ]);
        }

        return redirect()->back()->with('success', 'Đã xóa truyện khỏi danh sách theo dõi');
    }

    // Toggle bookmark notification
    public function toggleBookmarkNotification(Request $request)
    {
        $request->validate([
            'bookmark_id' => 'required|exists:bookmarks,id',
        ]);

        $bookmark = Bookmark::findOrFail($request->bookmark_id);

        // Check if the bookmark belongs to the current user
        if ($bookmark->user_id != Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn không có quyền thực hiện hành động này'
            ], 403);
        }

        $result = Bookmark::toggleNotification($request->bookmark_id);

        return response()->json($result);
    }

    private function processAndSaveAvatar($imageFile)
    {
        $now = Carbon::now();
        $yearMonth = $now->format('Y/m');
        $timestamp = $now->format('YmdHis');
        $randomString = Str::random(8);
        $fileName = "{$timestamp}_{$randomString}";

        // Create directories if they don't exist
        Storage::disk('public')->makeDirectory("avatars/{$yearMonth}/original");
        Storage::disk('public')->makeDirectory("avatars/{$yearMonth}/thumbnail");

        // Process original image
        $originalImage = Image::make($imageFile);
        $originalImage->encode('webp', 90);
        Storage::disk('public')->put(
            "avatars/{$yearMonth}/original/{$fileName}.webp",
            $originalImage->stream()
        );

        return [
            'original' => "avatars/{$yearMonth}/original/{$fileName}.webp",
        ];
    }

    public function updateAvatar(Request $request)
    {
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
            ], [
                'avatar.required' => 'Hãy chọn ảnh avatar',
                'avatar.image' => 'Avatar phải là ảnh',
                'avatar.mimes' => 'Chỉ chấp nhận ảnh định dạng jpeg, png, jpg hoặc gif',
                'avatar.max' => 'Dung lượng avatar không được vượt quá 4MB'
            ]);

            $user = Auth::user();
            DB::beginTransaction();

            try {
                // Store old avatar paths for deletion
                $oldAvatar = $user->avatar;
                $oldAvatarThumbnail = $user->avatar_thumbnail;

                // Process and save new avatar
                $avatarPaths = $this->processAndSaveAvatar($request->file('avatar'));

                // Update user avatar path
                $user->avatar = $avatarPaths['original'];
                $user->save();

                DB::commit();

                // Delete old avatars after successful update
                if ($oldAvatar) {
                    Storage::disk('public')->delete($oldAvatar);
                }
                if ($oldAvatarThumbnail) {
                    Storage::disk('public')->delete($oldAvatarThumbnail);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Cập nhật avatar thành công',
                    'avatar' => $avatarPaths['original'],
                    'avatar_url' => Storage::url($avatarPaths['original']),
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();

                // Delete new avatar if it was uploaded
                if (isset($avatarPaths)) {
                    Storage::disk('public')->delete([
                        $avatarPaths['original'],
                    ]);
                }

                Log::error('Avatar update error:', ['error' => $e->getMessage()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Có lỗi xảy ra, vui lòng thử lại sau'
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->errors()
            ], 422);
        }
    }

    public function updateNameOrPhone(Request $request)
    {

        if ($request->has('name')) {
            try {
                $request->validate([
                    'name' => 'required|string|min:3|max:255',
                ], [
                    'name.required' => 'Hãy nhập tên',
                    'name.string' => 'Tên phải là chuỗi',
                    'name.min' => 'Tên phải có ít nhất 3 ký tự',
                    'name.max' => 'Tên không được vượt quá 255 ký tự'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('user.profile')->with('error', $e->errors());
            }

            try {
                $user = Auth::user();
                $user->name = $request->name;
                $user->save();
                return redirect()->route('user.profile')->with('success', 'Cập nhật tên thành công');
            } catch (\Exception $e) {
                return redirect()->route('user.profile')->with('error', 'Cập nhật tên thất bại');
            }
        } elseif ($request->has('phone')) {

            try {
                $request->validate([
                    'phone' => 'required|string|min:10|max:10',
                ], [
                    'phone.required' => 'Hãy nhập số điện thoại',
                    'phone.string' => 'Số điện thoại phải là chuỗi',
                    'phone.min' => 'Số điện thoại phải có ít nhất 10 ký tự',
                    'phone.max' => 'Số điện thoại không được vượt quá 10 ký tự'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('user.profile')->with('error', $e->errors());
            }

            try {
                $user = Auth::user();
                $user->phone = $request->phone;
                $user->save();
                return redirect()->route('user.profile')->with('success', 'Cập nhật số điện thoại thành công');
            } catch (\Exception $e) {
                return redirect()->route('user.profile')->with('error', 'Cập nhật số điện thoại thất bại');
            }
        } elseif ($request->has('facebook_link')) {
            // Kiểm tra quyền - chỉ author và admin mới được update Facebook link
            $user = Auth::user();
            if ($user->role !== 'author' && $user->role !== 'admin') {
                return redirect()->route('user.profile')->with('error', 'Chỉ tác giả mới có thể cập nhật Facebook link');
            }

            try {
                $request->validate([
                    'facebook_link' => 'nullable|url|max:255',
                ], [
                    'facebook_link.url' => 'Link Facebook phải là URL hợp lệ',
                    'facebook_link.max' => 'Link Facebook không được vượt quá 255 ký tự'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->route('user.profile')->with('error', $e->errors());
            }

            try {
                $user->facebook_link = $request->facebook_link;
                $user->save();
                return redirect()->route('user.profile')->with('success', 'Cập nhật Facebook thành công');
            } catch (\Exception $e) {
                return redirect()->route('user.profile')->with('error', 'Cập nhật Facebook thất bại');
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ'
            ], 422);
        }
    }

    public function readingHistory()
    {
        // Get user reading history from database
        $readingHistory = UserReading::with([
            'story' => function ($query) {
                $query->withCount(['chapters' => function ($q) {
                    $q->where('status', 'published');
                }]);
            },
            'chapter'
        ])
            ->where('user_id', Auth::id())
            ->orderByDesc('updated_at')
            ->get();

        return view('pages.information.reading_history', compact('readingHistory'));
    }

    public function userPurchases()
    {
        // Get user's purchased chapters
        $purchasedChapters = Auth::user()->chapterPurchases()
            ->with(['chapter.story'])
            ->orderByDesc('created_at')
            ->get();

        // Get user's purchased story combos
        $purchasedStories = Auth::user()->storyPurchases()
            ->with(['story'])
            ->orderByDesc('created_at')
            ->get();

        return view('pages.information.purchases', compact('purchasedChapters', 'purchasedStories'));
    }

    public function clearReadingHistory()
    {
        // Delete all reading history for the current user
        UserReading::where('user_id', Auth::id())->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Lịch sử đọc truyện đã được xóa'
        ]);
    }

    public function loadMoreData(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $type = $request->type;
        $page = $request->page;

        switch ($type) {
            case 'deposits':
                $data = $user->deposits()
                    ->with('bank')
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'deposits_page', $page);
                break;
            case 'paypal-deposits':
                $data = $user->paypalDeposits()
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'paypal_deposits_page', $page);
                break;
            case 'card-deposits':
                $data = $user->cardDeposits()
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'card_deposits_page', $page);
                break;
            case 'story-purchases':
                $data = $user->storyPurchases()
                    ->with(['story'])
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'story_page', $page);
                break;
            case 'chapter-purchases':
                $data = $user->chapterPurchases()
                    ->with(['chapter.story'])
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'chapter_page', $page);
                break;
            case 'bookmarks':
                $data = $user->bookmarks()
                    ->with(['story', 'lastChapter'])
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'bookmarks_page', $page);
                break;
            case 'coin-transactions':
                $data = $user->coinTransactions()
                    ->with('admin')
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'coin_page', $page);
                break;
            case 'user-daily-tasks':
                $data = $user->userDailyTasks()
                    ->with('dailyTask')
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'daily_tasks_page', $page);
                break;
            case 'withdrawal-requests':
                $data = $user->withdrawalRequests()
                    ->with('processedBy')
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'withdrawals_page', $page);
                break;
            case 'author-chapter-earnings':
                $data = \App\Models\ChapterPurchase::whereHas('chapter.story', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['chapter.story', 'user'])
                ->orderByDesc('created_at')
                ->paginate(5, ['*'], 'author_chapter_earnings_page', $page);
                break;
            case 'author-story-earnings':
                $data = \App\Models\StoryPurchase::whereHas('story', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['story', 'user'])
                ->orderByDesc('created_at')
                ->paginate(5, ['*'], 'author_story_earnings_page', $page);
                break;
            case 'author-featured-stories':
                $data = \App\Models\StoryFeatured::where('user_id', $user->id)
                    ->where('type', \App\Models\StoryFeatured::TYPE_AUTHOR)
                    ->with(['story'])
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'author_featured_stories_page', $page);
                break;
            case 'author-stories':
                $data = \App\Models\Story::where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->paginate(5, ['*'], 'author_stories_page', $page);
                break;
            case 'coin-histories':
                $data = $user->coinHistories()
                    ->orderByDesc('created_at')
                    ->paginate(10, ['*'], 'coin_histories_page', $page);
                break;
            default:
                return response()->json(['error' => 'Invalid type'], 400);
        }

        return response()->json([
            'html' => view("admin.pages.users.partials.{$type}-table", [
                'data' => $data,
                'user' => $user
            ])->render(),
            'pagination' => $data->links('components.pagination')->toHtml(),
            'has_more' => $data->hasMorePages()
        ]);
    }
}
