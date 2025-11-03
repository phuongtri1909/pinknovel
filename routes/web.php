<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\DonateController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LogoSiteController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StoryComboController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\CardDepositController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\SocialController;
use App\Http\Controllers\PaypalDepositController;
use App\Http\Controllers\RequestPaymentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StoryReviewController;
use App\Http\Controllers\AuthorApplicationController;
use App\Http\Controllers\Admin\StoryTransferController;
use App\Http\Controllers\Admin\AdminDailyTaskController;
use App\Http\Controllers\Admin\BankController as AdminBankController;
use App\Http\Controllers\Admin\CardDepositController as AdminCardDepositController;
use App\Http\Controllers\Admin\PaypalDepositController as AdminPaypalDepositController;
use App\Http\Controllers\Admin\StoryEditRequestController as AdminStoryEditRequestController;
use App\Http\Controllers\Admin\AdminGuideController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/_test_cache', function () {
    return 'Time: ' . time() . ' | Path: ' . base_path();
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-main.xml', [SitemapController::class, 'main'])->name('sitemap.main');
Route::get('/sitemap-stories.xml', [SitemapController::class, 'stories'])->name('sitemap.stories');
Route::get('/sitemap-chapters.xml', [SitemapController::class, 'chapters'])->name('sitemap.chapters');
Route::get('/sitemap-categories.xml', [SitemapController::class, 'categories'])->name('sitemap.categories');




Route::post('/card-deposit/callback', [CardDepositController::class, 'callback'])->name('card.deposit.callback');


// Route::get('/check-card', [CardDepositController::class, 'checkCardForm'])->name('check.card.form');
// Route::post('/check-card', [CardDepositController::class, 'checkCard'])->name('check.card');

Route::middleware(['check.ip.ban', 'block.devtools'])->group(function () {
    Route::middleware(['check.ban:ban_login'])->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('/contact', [PageController::class, 'contact'])->name('contact');
        Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
        Route::get('/terms', [PageController::class, 'terms'])->name('terms');
        Route::get('/content-rules', [PageController::class, 'contentRules'])->name('content-rules');
        Route::get('/confidental', [PageController::class, 'confidental'])->name('confidental');

        Route::get('/search', [HomeController::class, 'searchHeader'])->name('searchHeader');
        Route::get('/tac-gia', [HomeController::class, 'searchAuthor'])->name('search.author');
        Route::get('/chuyen-ngu', [HomeController::class, 'searchTranslator'])->name('search.translator');
        Route::get('story-new-chapter', [HomeController::class, 'showStoryNewChapter'])->name('story.new.chapter');
        Route::get('story-hot', [HomeController::class, 'showStoryHot'])->name('story.hot');
        Route::get('story-rating', [HomeController::class, 'showRatingStories'])->name('story.rating');
        Route::get('story-new', [HomeController::class, 'showStoryNew'])->name('story.new');
        Route::get('story-view', [HomeController::class, 'showStoryView'])->name('story.view');
        Route::get('story-follow', [HomeController::class, 'showStoryFollow'])->name('story.follow');
        Route::get('story-completed', [HomeController::class, 'showCompletedStories'])->name('story.completed');

        Route::get('/categories-story/{slug}', [HomeController::class, 'showStoryCategories'])->name('categories.story.show');

        Route::get('story/{slug}', [HomeController::class, 'showStory'])->middleware('affiliate.redirect:story')->name('show.page.story');
        Route::get('/story/{storyId}/chapters', [HomeController::class, 'getStoryChapters'])->name('chapters.list');

        Route::get('/banner/{banner}', [BannerController::class, 'click'])
            ->middleware('affiliate.redirect:banner')
            ->name('banner.click');

        Route::middleware(['check.ban:ban_read'])->group(function () {
            Route::get('/story/{storySlug}/{chapterSlug}', [HomeController::class, 'chapterByStory'])->middleware('affiliate.redirect:chapter')->name('chapter');
            Route::post('/story/{storySlug}/{chapterSlug}/check-password', [HomeController::class, 'checkChapterPassword'])->name('chapter.check-password');
            Route::get('/search-chapters', [HomeController::class, 'searchChapters'])->name('chapters.search');
            Route::post('/reading/save-progress', [ReadingController::class, 'saveProgress'])
                ->name('reading.save-progress');
        });

        Route::post('/comments/{comment}/react', [CommentController::class, 'react'])->name('comments.react');
        Route::get('/stories/{storyId}/comments', [CommentController::class, 'loadComments'])->name('comments.load');

        // Guide routes
        Route::get('/huong-dan', [GuideController::class, 'index'])->name('guide.index');
        Route::get('/huong-dan/{slug}', [GuideController::class, 'show'])->name('guide.show');

        Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => 'auth'], function () {
            Route::get('profile', [UserController::class, 'userProfile'])->name('profile');
            Route::post('update-profile/update-name-or-phone', [UserController::class, 'updateNameOrPhone'])->name('update.name.or.phone');
            Route::post('update-avatar', [UserController::class, 'updateAvatar'])->name('update.avatar');
            Route::post('update-password', [UserController::class, 'updatePassword'])->name('update.password');
            Route::get('/reading-history', [UserController::class, 'readingHistory'])->name('reading.history');
            Route::post('/reading-history/clear', [UserController::class, 'clearReadingHistory'])->name('reading.history.clear');
            Route::get('purchases', [UserController::class, 'userPurchases'])->name('purchases');
            Route::get('/coin-history', [App\Http\Controllers\User\CoinHistoryController::class, 'index'])->name('coin-history');

            Route::get('/bookmarks', [BookmarkController::class, 'getUserBookmarks'])->name('bookmarks');
            Route::post('/bookmark/toggle', [BookmarkController::class, 'toggle'])->name('bookmark.toggle');
            Route::get('/bookmark/status', [BookmarkController::class, 'checkStatus'])->name('bookmark.status');
            Route::post('/bookmark/update-chapter', [BookmarkController::class, 'updateCurrentChapter'])->name('bookmark.update.chapter');
            Route::post('/bookmark/remove', [BookmarkController::class, 'remove'])->name('bookmark.remove');

            // Daily Tasks Routes
            Route::get('/daily-tasks', [App\Http\Controllers\DailyTaskController::class, 'index'])->name('daily-tasks');
            Route::post('/daily-tasks/complete/login', [App\Http\Controllers\DailyTaskController::class, 'completeLogin'])->name('daily-tasks.complete.login');
            Route::post('/daily-tasks/complete/comment', [App\Http\Controllers\DailyTaskController::class, 'completeComment'])->name('daily-tasks.complete.comment');
            Route::post('/daily-tasks/complete/bookmark', [App\Http\Controllers\DailyTaskController::class, 'completeBookmark'])->name('daily-tasks.complete.bookmark');
            Route::post('/daily-tasks/complete/share', [App\Http\Controllers\DailyTaskController::class, 'completeShare'])->name('daily-tasks.complete.share');
            Route::get('/daily-tasks/status', [App\Http\Controllers\DailyTaskController::class, 'getTodayStatus'])->name('daily-tasks.status');
            Route::get('/daily-tasks/history', [App\Http\Controllers\DailyTaskController::class, 'getHistory'])->name('daily-tasks.history');

            // Withdrawal routes
            Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
            Route::get('/withdrawals/create', [WithdrawalController::class, 'create'])->name('withdrawals.create');
            Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');

            // Deposit Routes
            Route::get('/deposit', [DepositController::class, 'index'])->name('deposit');
            // Request Payment Routes
            Route::post('/request-payment', [RequestPaymentController::class, 'store'])->name('request.payment.store');
            Route::post('/request-payment/confirm', [RequestPaymentController::class, 'confirm'])->name('request.payment.confirm');

            // Card Deposit Routes
            Route::get('/card-deposit', [CardDepositController::class, 'index'])->name('card.deposit');
            Route::post('/card-deposit', [CardDepositController::class, 'store'])->name('card.deposit.store');
            Route::get('/card-deposit/status/{id}', [CardDepositController::class, 'checkStatus'])->name('card.deposit.status');

            Route::get('/paypal-deposit', [PaypalDepositController::class, 'index'])->name('paypal.deposit');
            Route::post('/paypal-deposit', [PaypalDepositController::class, 'store'])->name('paypal.deposit.store');
            Route::post('/paypal-deposit/confirm', [PaypalDepositController::class, 'confirm'])->name('paypal.deposit.confirm');
            Route::get('/paypal-deposit/status/{transactionCode}', [PaypalDepositController::class, 'checkStatus'])->name('paypal.deposit.status');

            // Routes cho tác giả - sử dụng middleware 'role' mới
            Route::group(['middleware' => 'role:author,admin', 'prefix' => 'author', 'as' => 'author.'], function () {
                // Routes cho tác giả
                Route::get('/', [AuthorController::class, 'index'])->name('index');

                // Trang doanh thu
                Route::get('/revenue', [AuthorController::class, 'revenue'])->name('revenue');
                Route::get('/revenue/data', [AuthorController::class, 'getRevenueData'])->name('revenue.data');
                // Add new API routes for transaction history and top stories/chapters
                Route::get('/revenue/transactions', [AuthorController::class, 'getTransactionHistory'])->name('revenue.transactions');
                Route::get('/revenue/top-stories', [AuthorController::class, 'getTopStories'])->name('revenue.top-stories');
                Route::get('/revenue/top-chapters', [AuthorController::class, 'getTopChapters'])->name('revenue.top-chapters');
                Route::get('/revenue/story-stats', [AuthorController::class, 'getStoryRevenueStats'])->name('revenue.story-stats');

                Route::group(['prefix' => 'stories', 'as' => 'stories.'], function () {});

                Route::get('/stories', [AuthorController::class, 'stories'])->name('stories');
                Route::get('/stories/create', [AuthorController::class, 'create'])->name('stories.create');
                Route::post('/stories', [AuthorController::class, 'store'])->name('stories.store');
                Route::get('/stories/{story}/edit', [AuthorController::class, 'edit'])->name('stories.edit');
                Route::put('/stories/{story}', [AuthorController::class, 'update'])->name('stories.update');
                Route::delete('/stories/{story}', [AuthorController::class, 'destroy'])->name('stories.destroy');
                Route::post('/stories/{story}/submit-for-review', [AuthorController::class, 'submitForReview'])->name('stories.submit.for.review');


                Route::post('/stories/{story}/chapters/check-duplicates', [AuthorController::class, 'checkDuplicates'])
                    ->name('stories.chapters.check-duplicates');

                // Thêm route API để kiểm tra trạng thái truyện
                Route::get('/stories/{story}/check-status', [AuthorController::class, 'checkStoryStatus'])->name('stories.check.status');
                // Routes cho chương truyện
                Route::get('/stories/{story}/chapters', [AuthorController::class, 'showChapters'])->name('stories.chapters');
                Route::get('/stories/{story}/chapters/create', [AuthorController::class, 'createChapter'])->name('stories.chapters.create');
                Route::post('/stories/{story}/chapters', [AuthorController::class, 'storeChapter'])->name('stories.chapters.store');
                Route::get('/stories/{story}/chapters/{chapter}/edit', [AuthorController::class, 'editChapter'])->name('stories.chapters.edit');
                Route::put('/stories/{story}/chapters/{chapter}', [AuthorController::class, 'updateChapter'])->name('stories.chapters.update');
                Route::delete('/stories/{story}/chapters/{chapter}', [AuthorController::class, 'destroyChapter'])->name('stories.chapters.destroy');

                // cho việc thêm nhiều chương
                Route::get('/stories/{story}/chapters/batch/create', [AuthorController::class, 'createBatchChapters'])->name('stories.chapters.batch.create');
                Route::post('/stories/{story}/chapters/batch', [AuthorController::class, 'storeBatchChapters'])->name('stories.chapters.batch.store');

                Route::put('/stories/{story}/mark-complete', [AuthorController::class, 'markComplete'])->name('stories.mark-complete');
                Route::post('/stories/{story}/featured', [AuthorController::class, 'featured'])->name('stories.featured');

                Route::get('/stories/{story}/chapters/bulk-price', [AuthorController::class, 'bulkPriceForm'])->name('stories.chapters.bulk-price');
                Route::put('/stories/{story}/chapters/bulk-price/update', [AuthorController::class, 'bulkPriceUpdate'])->name('stories.chapters.bulk-price.update');

                Route::get('/stories/{story}/chapters/by-range', [AuthorController::class, 'getChaptersByRange'])->name('stories.chapters.by-range');

                Route::delete('/stories/{story}/chapters/bulk-delete/delete', [AuthorController::class, 'bulkDeleteChapters'])
                    ->name('stories.chapters.bulk-delete');

                Route::group(['prefix' => '/stories/combo', 'as' => 'stories.combo.'], function () {
                    Route::get('/create/{story}', [StoryComboController::class, 'create'])->name('create');
                    Route::post('/{story}', [StoryComboController::class, 'store'])->name('store');
                    Route::get('/edit/{story}', [StoryComboController::class, 'edit'])->name('edit');
                    Route::put('/{story}', [StoryComboController::class, 'update'])->name('update');
                    Route::delete('/{story}', [StoryComboController::class, 'destroy'])->name('destroy');
                });
            });

            // Author application routes
            Route::get('/author-application', [AuthorApplicationController::class, 'showApplicationForm'])->name('author.application');
            Route::post('/author-application', [AuthorApplicationController::class, 'submitApplication'])->name('author.submit');
        });

        Route::group(['middleware' => 'auth'], function () {

            Route::middleware(['check.ban:ban_comment'])->group(function () {
                Route::post('/comment/store', [CommentController::class, 'storeClient'])->name('comment.store.client');
            });

            Route::middleware(['check.ban:ban_rate'])->group(function () {
                Route::post('/ratings', [RatingController::class, 'storeClient'])->name('ratings.store');
                Route::get('/ratings', function () {
                    abort(404);
                });
            });

            // Routes for purchasing
            Route::post('/purchase/chapter', [PurchaseController::class, 'purchaseChapter'])->name('purchase.chapter');
            Route::post('/purchase/story-combo', [PurchaseController::class, 'purchaseStoryCombo'])->name('purchase.story.combo');

            Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

            Route::post('/comments/{commentId}/pin', [CommentController::class, 'togglePin'])->name('comments.pin');

            Route::group(['prefix' => 'admin'], function () {
                Route::group(['middleware' => 'role:admin'], function () {
                    Route::post('/users/{id}/banip', [UserController::class, 'banIp'])->name('users.banip');
                    Route::patch('/status/toggle', [StatusController::class, 'toggle'])->name('status.toggle');
                });

                // Sử dụng middleware 'role' thay vì 'role.admin.mod'
                Route::group(['middleware' => 'role:admin,mod'], function () {
                    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
                    Route::get('/dashboard/data', [DashboardController::class, 'getStatsData'])->name('admin.dashboard.data');

                    Route::get('users', [UserController::class, 'index'])->name('users.index');
                    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
                    Route::PATCH('users/{user}', [UserController::class, 'update'])->name('users.update');
                    Route::get('/users/{id}/load-more', [UserController::class, 'loadMoreData'])->name('users.load-more');

                    // Coin management routes
                    Route::get('coins', [CoinController::class, 'index'])->name('coins.index');
                    Route::get('coin-history', [App\Http\Controllers\Admin\CoinHistoryController::class, 'index'])->name('coin-history.index');
                    Route::get('coin-history/user/{userId}', [App\Http\Controllers\Admin\CoinHistoryController::class, 'showUser'])->name('coin-history.user');
                    Route::get('coins/{user}/create', [CoinController::class, 'create'])->name('coins.create');
                    Route::post('coins/{user}', [CoinController::class, 'store'])->name('coins.store');

                    Route::get('coin-transactions', [CoinController::class, 'transactions'])->name('coin.transactions');

                    Route::resource('categories', CategoryController::class);
                    Route::resource('stories', StoryController::class);
                    Route::patch('/stories/{story}/toggle-featured', [StoryController::class, 'toggleFeatured'])->name('stories.toggle-featured'); // NEW
                    Route::post('/stories/bulk-featured', [StoryController::class, 'bulkUpdateFeatured'])->name('stories.bulk-featured');


                    Route::resource('stories.chapters', ChapterController::class);


                    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
                    Route::get('comments', [CommentController::class, 'allComments'])->name('comments.all');
                    Route::delete('delete-comments/{comment}', [CommentController::class, 'deleteComment'])->name('delete.comments');
                    Route::post('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve')->middleware('role.admin.mod');
                    Route::post('comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject')->middleware('role.admin.mod');

                    Route::resource('banners', BannerController::class);

                    Route::get('logo-site', [LogoSiteController::class, 'edit'])->name('logo-site.edit');
                    Route::put('logo-site', [LogoSiteController::class, 'update'])->name('logo-site.update');

                    Route::get('/admin/donate', [DonateController::class, 'edit'])->name('donate.edit');
                    Route::put('/admin/donate', [DonateController::class, 'update'])->name('donate.update');

                    // Duyệt truyện
                    Route::post('/stories/{story}/approve', [AuthorController::class, 'approve'])->name('stories.approve');

                    // Quản lý giao dịch nạp xu
                    Route::get('/deposits', [DepositController::class, 'adminIndex'])->name('deposits.index');
                    Route::post('/deposits/{deposit}/approve', [DepositController::class, 'approve'])->name('deposits.approve');
                    Route::post('/deposits/{deposit}/reject', [DepositController::class, 'reject'])->name('deposits.reject');

                    // Quản lý yêu cầu thanh toán
                    Route::get('/request-payments', [RequestPaymentController::class, 'adminIndex'])->name('request.payments.index');
                    Route::post('/request-payments/delete-expired', [RequestPaymentController::class, 'deleteExpired'])->name('request.payments.delete-expired');



                    Route::group(['as' => 'admin.'], function () {

                        Route::get('/story-transfer', [StoryTransferController::class, 'index'])->name('story-transfer.index');
                        Route::get('/story-transfer/history', [StoryTransferController::class, 'history'])->name('story-transfer.history');
                        Route::get('/story-transfer/history/{history}', [StoryTransferController::class, 'historyShow'])->name('story-transfer.history.show');
                        Route::get('/story-transfer/{story}', [StoryTransferController::class, 'show'])->name('story-transfer.show');
                        Route::post('/story-transfer/{story}', [StoryTransferController::class, 'transfer'])->name('story-transfer.transfer');
                        Route::post('/story-transfer-bulk', [StoryTransferController::class, 'bulkTransfer'])->name('story-transfer.bulk');
                        Route::get('/api/author-stories', [StoryTransferController::class, 'getAuthorStories'])->name('story-transfer.author-stories');

                        // Quản lý ngân hàng
                        Route::resource('banks', AdminBankController::class);

                        // Quản lý cấu hình hệ thống
                        Route::resource('configs', ConfigController::class);

                        // Quản lý Card Deposit
                        Route::get('/card-deposits', [AdminCardDepositController::class, 'adminIndex'])->name('card-deposits.index');

                        // Quản lý PayPal Deposit
                        Route::get('/paypal-deposits', [AdminPaypalDepositController::class, 'adminIndex'])->name('paypal-deposits.index');
                        Route::post('/paypal-deposits/{deposit}/approve', [AdminPaypalDepositController::class, 'approve'])->name('paypal-deposits.approve');
                        Route::post('/paypal-deposits/{deposit}/reject', [AdminPaypalDepositController::class, 'reject'])->name('paypal-deposits.reject');

                        // Quản lý Request Payment PayPal
                        Route::get('/request-payment-paypal', [AdminPaypalDepositController::class, 'requestPaymentIndex'])->name('request-payment-paypal.index');
                        Route::post('/request-payment-paypal/delete-expired', [AdminPaypalDepositController::class, 'deleteExpiredRequests'])->name('request-payment-paypal.delete-expired');

                        // Author application management routes
                        Route::get('/author-applications', [AuthorApplicationController::class, 'listApplications'])->name('author-applications.index');
                        Route::get('/author-applications/{application}', [AuthorApplicationController::class, 'showApplication'])->name('author-applications.show');
                        Route::post('/author-applications/{application}/approve', [AuthorApplicationController::class, 'approveApplication'])->name('author-applications.approve');
                        Route::post('/author-applications/{application}/reject', [AuthorApplicationController::class, 'rejectApplication'])->name('author-applications.reject');

                        // Story review routes
                        Route::get('/story-reviews', [StoryReviewController::class, 'index'])->name('story-reviews.index');
                        Route::get('/story-reviews/{story}', [StoryReviewController::class, 'show'])->name('story-reviews.show');
                        Route::post('/story-reviews/{story}/approve', [StoryReviewController::class, 'approve'])->name('story-reviews.approve');
                        Route::post('/story-reviews/{story}/reject', [StoryReviewController::class, 'reject'])->name('story-reviews.reject');

                        // Edit request routes
                        Route::get('/edit-requests', [AdminStoryEditRequestController::class, 'index'])->name('edit-requests.index');
                        Route::get('/edit-requests/{editRequest}', [AdminStoryEditRequestController::class, 'show'])->name('edit-requests.show');
                        Route::post('/edit-requests/{editRequest}/approve', [AdminStoryEditRequestController::class, 'approve'])->name('edit-requests.approve');
                        Route::post('/edit-requests/{editRequest}/reject', [AdminStoryEditRequestController::class, 'reject'])->name('edit-requests.reject');

                        // Guide management
                        Route::resource('guides', AdminGuideController::class);
                        Route::post('/guides/upload-image', [AdminGuideController::class, 'uploadImage'])->name('guides.upload-image');

                        // Withdrawal management
                        Route::get('/withdrawals', [WithdrawalController::class, 'adminIndex'])->name('withdrawals.index');
                        Route::get('/withdrawals/{withdrawal}', [WithdrawalController::class, 'adminShow'])->name('withdrawals.show');
                        Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalController::class, 'approve'])->name('withdrawals.approve');
                        Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

                        // Social Media Management
                        Route::get('socials', [SocialController::class, 'index'])->name('socials.index');
                        Route::post('socials', [SocialController::class, 'store'])->name('socials.store');
                        Route::put('socials/{social}', [SocialController::class, 'update'])->name('socials.update');
                        Route::delete('socials/{social}', [SocialController::class, 'destroy'])->name('socials.destroy');

                        // Daily Tasks Management
                        Route::resource('daily-tasks', AdminDailyTaskController::class)->except('create', 'store', 'destroy');
                        Route::post('daily-tasks/{dailyTask}/toggle-active', [AdminDailyTaskController::class, 'toggleActive'])->name('daily-tasks.toggle-active');
                        Route::get('daily-tasks/dt/user-progress', [AdminDailyTaskController::class, 'userProgress'])->name('daily-tasks.user-progress');
                        Route::get('daily-tasks/dt/statistics', [AdminDailyTaskController::class, 'statistics'])->name('daily-tasks.statistics');
                    });
                });
            });
        });

        Route::group(['middleware' => 'guest'], function () {
            Route::get('/login', function () {
                return view('pages.auth.login');
            })->name('login');
            Route::post('/login', [AuthController::class, 'login'])->name('login');

            Route::get('/register', function () {
                return view('pages.auth.register');
            })->name('register');
            Route::post('/register', [AuthController::class, 'register'])->name('register');

            Route::get('/forgot-password', function () {
                return view('pages.auth.forgot-password');
            })->name('forgot-password');
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot.password');

            Route::group(['prefix' => 'admin'], function () {
                Route::get('/login', function () {
                    return view('admin.pages.auth.login');
                })->name('admin.login');
            });

            Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('login.google');
            Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
        });
    });
});
