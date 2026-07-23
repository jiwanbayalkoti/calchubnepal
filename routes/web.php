<?php

use App\Http\Controllers\Web\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Web\Account\FavoriteController as AccountFavoriteController;
use App\Http\Controllers\Web\Account\HistoryController as AccountHistoryController;
use App\Http\Controllers\Web\Account\ProfileController as AccountProfileController;
use App\Http\Controllers\Web\Account\SavedCalculationController as AccountSavedCalculationController;
use App\Http\Controllers\Web\Account\SubscriptionController as AccountSubscriptionController;
use App\Http\Controllers\Web\AdTrackingController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CalculatorController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\FeedbackController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website Routes
|--------------------------------------------------------------------------
|
| Renders the marketing/public front-end (home, calculators, categories,
| blog, search, static pages). All business logic lives in the Service
| layer (CalculatorServiceInterface, SeoService, SettingsService,
| AiServiceInterface) - controllers below stay thin.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/calculators', [CalculatorController::class, 'index'])->name('calculators.index');
Route::get('/calculator/{calculator}', [CalculatorController::class, 'show'])->name('calculators.show');

Route::middleware('throttle:30,1')->group(function () {
    Route::post('/calculator/{calculator}/calculate', [CalculatorController::class, 'calculate'])->name('calculators.calculate');
    Route::post('/calculator/{calculator}/explain', [CalculatorController::class, 'explain'])->name('calculators.explain');
    Route::post('/calculator/{calculator}/pdf', [CalculatorController::class, 'pdf'])->name('calculators.pdf');
});

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/category/{category}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/search', [SearchController::class, 'results'])->name('search.results');
Route::get('/api/search/suggest', [SearchController::class, 'suggest'])
    ->middleware('throttle:60,1')
    ->name('search.suggest');

Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'contactStore'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::post('/feedback', [FeedbackController::class, 'store'])
    ->middleware('throttle:8,1')
    ->name('feedback.store');

Route::middleware('throttle:120,1')->group(function () {
    Route::get('/ads/adsense/impression', [AdTrackingController::class, 'adsenseImpression'])->name('ads.adsense.impression');
    Route::get('/ads/{id}/impression', [AdTrackingController::class, 'impression'])->name('ads.impression');
    Route::get('/ads/{id}/click', [AdTrackingController::class, 'click'])->name('ads.click');
});

Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms-conditions', [PageController::class, 'terms'])->name('terms');
Route::get('/cookie-policy', [PageController::class, 'cookies'])->name('cookies');
Route::get('/disclaimer', [PageController::class, 'disclaimer'])->name('disclaimer');
Route::get('/sitemap', [PageController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.xml');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Authenticated User Account
|--------------------------------------------------------------------------
|
| Member area for normal users (dashboard, history, favorites, saves,
| profile, subscription). Admins still use /admin separately.
*/

Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [AccountProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [AccountProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [AccountProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/history', [AccountHistoryController::class, 'index'])->name('history.index');
    Route::delete('/history/{history}', [AccountHistoryController::class, 'destroy'])->name('history.destroy');
    Route::delete('/history', [AccountHistoryController::class, 'clear'])->name('history.clear');

    Route::get('/favorites', [AccountFavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{calculator}/toggle', [AccountFavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::delete('/favorites/{favorite}', [AccountFavoriteController::class, 'destroy'])->name('favorites.destroy');

    Route::get('/saved', [AccountSavedCalculationController::class, 'index'])->name('saved.index');
    Route::post('/saved', [AccountSavedCalculationController::class, 'store'])->name('saved.store');
    Route::get('/saved/{saved}', [AccountSavedCalculationController::class, 'show'])->name('saved.show');
    Route::delete('/saved/{saved}', [AccountSavedCalculationController::class, 'destroy'])->name('saved.destroy');

    Route::get('/subscription', [AccountSubscriptionController::class, 'index'])->name('subscription');
    Route::post('/subscription/interest', [AccountSubscriptionController::class, 'requestPlan'])
        ->middleware('throttle:5,1')
        ->name('subscription.interest');
});

Route::get('/dashboard', fn () => redirect()->route('account.dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', fn () => redirect()->route('account.profile.edit'))->name('profile.edit');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/advertiser.php';
