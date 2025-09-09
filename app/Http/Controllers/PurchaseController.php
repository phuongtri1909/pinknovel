<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Story;
use App\Models\Config;
use App\Models\Chapter;
use Illuminate\Http\Request;
use App\Models\StoryPurchase;
use App\Models\ChapterPurchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * Constructor - ensure user is authenticated
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Purchase a chapter
     */
    public function purchaseChapter(Request $request)
    {
        try {
            // Validate request data
            $validator = validator($request->all(), [
                'chapter_id' => 'required|exists:chapters,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ: ' . $validator->errors()->first()
                ], 422);
            }

            $user = Auth::user();
            $chapter = Chapter::findOrFail($request->chapter_id);

            if (!$chapter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chương không tồn tại.'
                ], 404);
            }

            $story = $chapter->story;

            // Admin và mod có quyền xem tất cả truyện mà không cần mua
            if (in_array($user->role, ['admin', 'mod'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn có quyền quản trị, không cần mua chương này.',
                    'redirect' => route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug])
                ]);
            }

            // Author không cần mua chương của truyện mình
            if ($user->role == 'author' && $story->user_id == $user->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đây là truyện của bạn, không cần mua chương này.',
                    'redirect' => route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug])
                ]);
            }

            // Check if chapter is already free
            if (!$chapter->price || $chapter->price == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chương này đã miễn phí, không cần mua.',
                    'redirect' => route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug])
                ]);
            }

            // Check đã mua combo truyện
            $storyPurchase = StoryPurchase::where('user_id', $user->id)
                ->where('story_id', $chapter->story_id)
                ->exists();

            if ($storyPurchase) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn đã mua trọn bộ truyện này, có thể đọc tất cả các chương.',
                    'redirect' => route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug])
                ]);
            }

            // Kiểm tra xem người dùng đã mua chương này chưa
            $existingPurchase = ChapterPurchase::where('user_id', $user->id)
                ->where('chapter_id', $chapter->id)
                ->first();

            if ($existingPurchase) {
                // Người dùng đã mua chương này trước đó, trả về thành công
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn đã mua chương này trước đó.',
                    'redirect' => route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug])
                ]);
            }

            // Kiểm tra xem người dùng có đủ xu để mua chương này không
            if ($user->coins < $chapter->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không đủ xu để mua chương này. Vui lòng nạp thêm.',
                    'redirect' => route('user.deposit')
                ], 400);
            }

            $success = false;

            try {
                DB::beginTransaction();

                // Kiểm tra lại số dư
                if ($user->coins < $chapter->price) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Số dư không đủ để mua chương này.',
                        'redirect' => route('user.deposit')
                    ], 400);
                }

                // cộng tiền cho tác giả
                $authorPercentage = $story->is_monopoly ? Config::getConfig('monopoly_author_percentage', 90) : Config::getConfig('non_monopoly_author_percentage', 70);
                $rawEarnings = ($chapter->price * $authorPercentage) / 100;
                $authorEarnings = round($rawEarnings);

                // Create purchase record với amount_received đã tính
                $purchase = ChapterPurchase::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'chapter_id' => $chapter->id
                    ],
                    [
                        'amount_paid' => $chapter->price,
                        'amount_received' => $authorEarnings,
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );

                // Sử dụng CoinService để chuyển xu
                $coinService = new \App\Services\CoinService();
                $coinService->transferCoins(
                    $user,
                    $story->user,
                    $chapter->price,
                    \App\Models\CoinHistory::TYPE_CHAPTER_PURCHASE,
                    "Mua chương: {$chapter->title}",
                    $purchase
                );
                DB::commit();
                $success = true;
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                Log::error('Lỗi khi mua chương 1: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xử lý giao dịch. Vui lòng thử lại.'
                ], 500);
            }

            // Nếu thành công
            if ($success) {
                // Nếu là request AJAX, trả về JSON
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Mua chương thành công! Đang tải nội dung...',
                        'newBalance' => $user->coins,
                        'redirect' => route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug])
                    ]);
                }

                // Nếu là form submit thông thường, redirect với thông báo
                return redirect()->route('chapter', ['storySlug' => $story->slug, 'chapterSlug' => $chapter->slug])
                    ->with('success', 'Mua chương thành công!');
            }

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý giao dịch. Vui lòng thử lại.'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi mua chương 2: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý giao dịch. Vui lòng thử lại.'
            ], 500);
        }
    }

    /**
     * Purchase a story combo (all chapters)
     */
    public function purchaseStoryCombo(Request $request)
    {
        try {
            // Validate request data
            $validator = validator($request->all(), [
                'story_id' => 'required|exists:stories,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ: ' . $validator->errors()->first()
                ], 422);
            }

            $user = Auth::user();
            $story = Story::findOrFail($request->story_id);

            // Admin và mod có quyền xem tất cả truyện mà không cần mua
            if (in_array($user->role, ['admin', 'mod'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn có quyền quản trị, không cần mua truyện này.',
                    'redirect' => route('show.page.story', $story->slug)
                ]);
            }

            // Author không cần mua truyện của mình
            if ($user->role == 'author' && $story->user_id == $user->id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đây là truyện của bạn, không cần mua combo này.',
                    'redirect' => route('show.page.story', $story->slug)
                ]);
            }

            // Check if story has combo option enabled
            if (!$story->has_combo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Truyện này không có gói combo.'
                ], 400);
            }

            // Check if user already purchased this story combo
            $existingPurchase = StoryPurchase::where('user_id', $user->id)
                ->where('story_id', $story->id)
                ->first();

            if ($existingPurchase) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn đã mua combo truyện này trước đó.',
                    'redirect' => route('show.page.story', $story->slug)
                ]);
            }

            // Check if user has enough coins
            if ($user->coins < $story->combo_price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không đủ xu để mua combo này. Vui lòng nạp thêm.',
                    'redirect' => route('user.deposit')
                ], 400);
            }

            $success = false;


            try {
                DB::beginTransaction();

                // Khóa dòng người dùng để tránh race condition
                $freshUser = User::lockForUpdate()->find($user->id);

                // Kiểm tra lại một lần nữa xem combo đã được mua chưa
                $purchaseExists = StoryPurchase::where('user_id', $user->id)
                    ->where('story_id', $story->id)
                    ->exists();

                if ($purchaseExists) {
                    // Người dùng đã mua combo này trong khi đang xử lý
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'Bạn đã mua combo truyện này.',
                        'redirect' => route('show.page.story', $story->slug)
                    ]);
                }

                // Kiểm tra lại số dư
                if ($freshUser->coins < $story->combo_price) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Số dư không đủ để mua combo này.',
                        'redirect' => route('user.deposit')
                    ], 400);
                }

                // Cộng tiền cho tác giả
                $authorPercentage = $story->is_monopoly ? Config::getConfig('monopoly_author_percentage', 90) : Config::getConfig('non_monopoly_author_percentage', 70);
                $rawEarnings = ($story->combo_price * $authorPercentage) / 100;
                $authorEarnings = round($rawEarnings);

                // Create purchase record
                $purchase = StoryPurchase::create([
                    'user_id' => $user->id,
                    'story_id' => $story->id,
                    'amount_paid' => $story->combo_price,
                    'amount_received' => $authorEarnings,
                ]);

                // Sử dụng CoinService để chuyển xu
                $coinService = new \App\Services\CoinService();
                $coinService->transferCoins(
                    $freshUser,
                    $story->user,
                    $story->combo_price,
                    \App\Models\CoinHistory::TYPE_STORY_PURCHASE,
                    "Mua combo truyện: {$story->title}",
                    $purchase
                );

                DB::commit();
                $success = true;

                // Cập nhật số dư mới cho user instance
                $user->coins = $freshUser->coins;
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                // Cho là đã mua thành công
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn đã mua combo truyện này.',
                    'newBalance' => $freshUser->coins,
                    'redirect' => route('show.page.story', $story->slug)
                ]);
            }


            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mua combo truyện thành công! Đang tải nội dung...',
                    'newBalance' => $user->coins,
                    'redirect' => route('show.page.story', $story->slug)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý giao dịch. Vui lòng thử lại.'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi mua combo truyện: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý giao dịch. Vui lòng thử lại.'
            ], 500);
        }
    }
}
