<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
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
use App\Http\Controllers\SocialsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LogoSiteController;
use App\Http\Controllers\StoryComboController;
use App\Http\Controllers\RecentlyReadController;
use App\Http\Controllers\CommentReactionController;
use App\Http\Controllers\StoryEditRequestController;
use App\Http\Controllers\AuthorApplicationController;
use App\Http\Controllers\Admin\BankController as AdminBankController;

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

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-main.xml', [SitemapController::class, 'main'])->name('sitemap.main');
Route::get('/sitemap-stories.xml', [SitemapController::class, 'stories'])->name('sitemap.stories');
Route::get('/sitemap-chapters.xml', [SitemapController::class, 'chapters'])->name('sitemap.chapters');
Route::get('/sitemap-categories.xml', [SitemapController::class, 'categories'])->name('sitemap.categories');

Route::group(['middleware' => 'check.ip.ban'], function () {
    Route::middleware(['check.ban:ban_login'])->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('/search', [HomeController::class, 'searchHeader'])->name('searchHeader');
        Route::get('/tac-gia', [HomeController::class, 'searchAuthor'])->name('search.author');
        Route::get('/chuyen-ngu', [HomeController::class, 'searchTranslator'])->name('search.translator');

        Route::get('/categories-story/{slug}', [HomeController::class, 'showStoryCategories'])->name('categories.story.show');

        Route::get('story/{slug}', [HomeController::class, 'showStory'])->middleware('affiliate.redirect:story')->name('show.page.story');
        Route::get('/story/{storyId}/chapters', [HomeController::class, 'getStoryChapters'])->name('chapters.list');

        Route::get('/banner/{banner}', [BannerController::class, 'click'])
            ->middleware('affiliate.redirect:banner')
            ->name('banner.click');

        Route::middleware(['check.ban:ban_read'])->group(function () {
            Route::get('/story/{storySlug}/{chapterSlug}', [HomeController::class, 'chapterByStory'])->middleware('affiliate.redirect:chapter')->name('chapter');
            Route::get('/search-chapters', [HomeController::class, 'searchChapters'])->name('chapters.search');
            Route::post('/reading/save-progress', [ReadingController::class, 'saveProgress'])
                ->name('reading.save-progress');
        });

        Route::post('/comments/{comment}/react', [CommentController::class, 'react'])->name('comments.react');
        Route::get('/stories/{storyId}/comments', [CommentController::class, 'loadComments'])->name('comments.load');

        Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => 'auth'], function () {
            Route::get('profile', [UserController::class, 'userProfile'])->name('profile');
            Route::post('update-profile/update-name-or-phone', [UserController::class, 'updateNameOrPhone'])->name('update.name.or.phone');
            Route::post('update-avatar', [UserController::class, 'updateAvatar'])->name('update.avatar');
            Route::post('update-password', [UserController::class, 'updatePassword'])->name('update.password');
            Route::get('/reading-history', [UserController::class, 'readingHistory'])->name('reading.history');
            Route::post('/reading-history/clear', [UserController::class, 'clearReadingHistory'])->name('reading.history.clear');
            Route::post('/track-reading', [ReadingController::class, 'trackReading'])->name('track.reading');

            Route::get('/bookmarks', [UserController::class, 'bookmarks'])->name('bookmarks');
            Route::post('/bookmark/toggle', [UserController::class, 'toggleBookmark'])->name('bookmark.toggle');
            Route::post('/bookmark/remove', [UserController::class, 'removeBookmark'])->name('bookmark.remove');
            Route::post('/bookmark/notification', [UserController::class, 'toggleBookmarkNotification'])->name('bookmark.notification');

            // Deposit Routes
            Route::get('/deposit', [DepositController::class, 'index'])->name('deposit');
            Route::post('/deposit/validate', [DepositController::class, 'validatePayment'])->name('deposit.validate');
            Route::post('/deposit', [DepositController::class, 'store'])->name('deposit.store');

            // Routes cho tác giả - sử dụng middleware 'role' mới
            Route::group(['middleware' => 'role:author,admin', 'prefix' => 'author', 'as' => 'author.'], function () {
                // Routes cho tác giả
                Route::get('/', [AuthorController::class, 'index'])->name('index');

                Route::group(['prefix' => 'stories', 'as' => 'stories.'], function () {
                    
                });

                Route::get('/stories', [AuthorController::class, 'stories'])->name('stories');
                Route::get('/stories/create', [AuthorController::class, 'create'])->name('stories.create');
                Route::post('/stories', [AuthorController::class, 'store'])->name('stories.store');
                Route::get('/stories/{story}/edit', [AuthorController::class, 'edit'])->name('stories.edit');
                Route::put('/stories/{story}', [AuthorController::class, 'update'])->name('stories.update');
                Route::delete('/stories/{story}', [AuthorController::class, 'destroy'])->name('stories.destroy');
                Route::post('/stories/{story}/submit-for-review', [AuthorController::class, 'submitForReview'])->name('stories.submit.for.review');

                // Thêm route API để kiểm tra trạng thái truyện
                Route::get('/stories/{story}/check-status', [AuthorController::class, 'checkStoryStatus'])->name('stories.check.status');
                // Routes cho chương truyện
                Route::get('/stories/{story}/chapters', [AuthorController::class, 'showChapters'])->name('stories.chapters');
                Route::get('/stories/{story}/chapters/create', [AuthorController::class, 'createChapter'])->name('stories.chapters.create');
                Route::post('/stories/{story}/chapters', [AuthorController::class, 'storeChapter'])->name('stories.chapters.store');
                Route::get('/stories/{story}/chapters/{chapter}/edit', [AuthorController::class, 'editChapter'])->name('stories.chapters.edit');
                Route::put('/stories/{story}/chapters/{chapter}', [AuthorController::class, 'updateChapter'])->name('stories.chapters.update');
                Route::delete('/stories/{story}/chapters/{chapter}', [AuthorController::class, 'destroyChapter'])->name('stories.chapters.destroy');

                Route::put('/stories/{story}/mark-complete', [AuthorController::class, 'markComplete'])->name('stories.mark-complete');

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
                Route::post('comment/store', [CommentController::class, 'storeClient'])->name('comment.store.client');
            });

            Route::middleware(['check.ban:ban_rate'])->group(function () {
                Route::post('/ratings', [RatingController::class, 'storeClient'])->name('ratings.store');
                Route::get('/ratings', function () {
                    abort(404);
                });
            });

            Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

            // Sử dụng middleware 'role' thay vì 'role.admin'
            Route::group(['middleware' => 'role:admin'], function () {
                Route::post('/comments/{comment}/pin', [CommentController::class, 'togglePin'])->name('comments.pin');
            });

            Route::group(['prefix' => 'admin'], function () {
                // Sử dụng middleware 'role' thay vì 'role.admin'
                Route::group(['middleware' => 'role:admin'], function () {
                    Route::post('/users/{id}/banip', [UserController::class, 'banIp'])->name('users.banip');
                    Route::patch('/status/toggle', [StatusController::class, 'toggle'])->name('status.toggle');
                });

                // Sử dụng middleware 'role' thay vì 'role.admin.mod'
                Route::group(['middleware' => 'role:admin,mod'], function () {
                    Route::get('/dashboard', function () {
                        return view('admin.pages.dashboard');
                    })->name('admin.dashboard');

                    Route::get('users', [UserController::class, 'index'])->name('users.index');
                    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
                    Route::PATCH('users/{user}', [UserController::class, 'update'])->name('users.update');

                    Route::resource('categories', CategoryController::class);
                    Route::resource('stories', StoryController::class);
                    Route::resource('stories.chapters', ChapterController::class);

                    Route::get('stories/{story}/comments', [CommentController::class, 'index'])->name('stories.comments.index');
                    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

                    Route::delete('delete-comments/{comment}', [CommentController::class, 'deleteComment'])->name('delete.comments');

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


                    // Story Edit Requests
                    Route::get('/edit-requests', [StoryEditRequestController::class, 'index'])->name('edit-requests.index');
                    Route::get('/edit-requests/{editRequest}', [StoryEditRequestController::class, 'show'])->name('edit-requests.show');
                    Route::post('/edit-requests/{editRequest}/approve', [StoryEditRequestController::class, 'approve'])->name('edit-requests.approve');
                    Route::post('/edit-requests/{editRequest}/reject', [StoryEditRequestController::class, 'reject'])->name('edit-requests.reject');

                    // Author application management routes
                    Route::get('/author-applications', [AuthorApplicationController::class, 'listApplications'])->name('admin.author-applications.index');
                    Route::get('/author-applications/{application}', [AuthorApplicationController::class, 'showApplication'])->name('admin.author-applications.show');
                    Route::post('/author-applications/{application}/approve', [AuthorApplicationController::class, 'approveApplication'])->name('admin.author-applications.approve');
                    Route::post('/author-applications/{application}/reject', [AuthorApplicationController::class, 'rejectApplication'])->name('admin.author-applications.reject');

                    Route::group(['as' => 'admin.'], function () {
                        // Quản lý ngân hàng
                        Route::resource('banks', AdminBankController::class);
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
