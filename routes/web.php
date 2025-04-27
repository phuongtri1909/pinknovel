<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\DonateController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SocialsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LogoSiteController;
use App\Http\Controllers\RecentlyReadController;
use App\Http\Controllers\CommentReactionController;

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
        Route::get('/search', [HomeController::class,'searchHeader'])->name('searchHeader');

        // Route for viewing stories by category
        Route::get('/categories-story/{slug}', [HomeController::class,'showStoryCategories'])->name('categories.story.show');

        Route::get('story/{slug}', [HomeController::class, 'showStory'])->middleware('affiliate.redirect:story')->name('show.page.story');

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

        Route::get('profile', [UserController::class, 'userProfile'])->name('profile');
        Route::post('update-profile/update-name-or-phone', [UserController::class, 'updateNameOrPhone'])->name('update.name.or.phone');
        Route::post('update-avatar', [UserController::class, 'updateAvatar'])->name('update.avatar');
        Route::post('update-password', [UserController::class, 'updatePassword'])->name('update.password');

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

            Route::group(['middleware' => 'role.admin'], function () {
                Route::post('/comments/{comment}/pin', [CommentController::class, 'togglePin'])->name('comments.pin');
            });

            Route::group(['prefix' => 'admin'], function () {
                Route::group(['middleware' => 'role.admin'], function () {
                    Route::post('/users/{id}/banip', [UserController::class, 'banIp'])->name('users.banip');
                    Route::patch('/status/toggle', [StatusController::class, 'toggle'])->name('status.toggle');
                });

                Route::group(['middleware' => 'role.admin.mod'], function () {

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
        });
    });
});
