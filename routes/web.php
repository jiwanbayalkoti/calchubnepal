<?php

use App\Http\Controllers\Web\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Web\Account\DynamicQrController as AccountDynamicQrController;
use App\Http\Controllers\Web\Account\FavoriteController as AccountFavoriteController;
use App\Http\Controllers\Web\Account\HistoryController as AccountHistoryController;
use App\Http\Controllers\Web\Account\ProfileController as AccountProfileController;
use App\Http\Controllers\Web\Account\SavedCalculationController as AccountSavedCalculationController;
use App\Http\Controllers\Web\Account\SubscriptionController as AccountSubscriptionController;
use App\Http\Controllers\Web\Account\ApiKeyController as AccountApiKeyController;
use App\Http\Controllers\Web\Account\BrandTemplateController as AccountBrandTemplateController;
use App\Http\Controllers\Web\Account\BulkQrController as AccountBulkQrController;
use App\Http\Controllers\Web\Account\CampaignController as AccountCampaignController;
use App\Http\Controllers\Web\Account\QrEnterpriseDashboardController as AccountQrEnterpriseDashboardController;
use App\Http\Controllers\Web\Account\WorkspaceController as AccountWorkspaceController;
use App\Http\Controllers\Web\AdTrackingController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CalculatorController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\FeedbackController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\QrCodeGeneratorController;
use App\Http\Controllers\Web\QrRedirectController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\VisitingCardController;
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

Route::get('/qr-code-generator', [QrCodeGeneratorController::class, 'show'])->name('qr-code-generator');
Route::middleware('throttle:60,1')->prefix('qr-code-generator')->name('qr-code-generator.')->group(function () {
    Route::post('/preview', [QrCodeGeneratorController::class, 'preview'])->name('preview');
    Route::post('/download', [QrCodeGeneratorController::class, 'download'])->name('download');
    Route::post('/logo', [QrCodeGeneratorController::class, 'uploadLogo'])->name('logo');
    Route::get('/recent', [QrCodeGeneratorController::class, 'recent'])->name('recent');
    Route::get('/saved', [QrCodeGeneratorController::class, 'saved'])->name('saved');
    Route::post('/{uuid}/save', [QrCodeGeneratorController::class, 'save'])->name('save');
    Route::delete('/{uuid}', [QrCodeGeneratorController::class, 'destroy'])->name('destroy');
    Route::post('/dynamic', [QrCodeGeneratorController::class, 'createDynamic'])
        ->middleware('auth')
        ->name('dynamic');
});

Route::middleware('throttle:120,1')->group(function () {
    Route::get('/q/{code}', QrRedirectController::class)
        ->where('code', '[A-Za-z0-9]{4,16}')
        ->name('qr.redirect');
    Route::get('/q/{code}/unlock', [QrRedirectController::class, 'unlockForm'])
        ->where('code', '[A-Za-z0-9]{4,16}')
        ->name('qr.unlock');
    Route::post('/q/{code}/unlock', [QrRedirectController::class, 'unlock'])
        ->where('code', '[A-Za-z0-9]{4,16}')
        ->middleware('throttle:20,1')
        ->name('qr.unlock.submit');
});

Route::get('/visiting-card-designer', [VisitingCardController::class, 'show'])->name('visiting-card-designer');
Route::middleware('throttle:60,1')->prefix('visiting-card-designer')->name('visiting-card-designer.')->group(function () {
    Route::post('/preview', [VisitingCardController::class, 'preview'])->name('preview');
    Route::post('/download', [VisitingCardController::class, 'download'])->name('download');
    Route::post('/logo', [VisitingCardController::class, 'uploadLogo'])->name('logo');
});

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
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

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
    Route::post('/subscription/checkout', [AccountSubscriptionController::class, 'checkout'])
        ->middleware('throttle:10,1')
        ->name('subscription.checkout');
    Route::get('/billing/success/{transaction}', [AccountSubscriptionController::class, 'billingSuccess'])
        ->name('billing.success');

    Route::get('/qr-enterprise', AccountQrEnterpriseDashboardController::class)->name('qr-enterprise');

    Route::get('/workspaces', [AccountWorkspaceController::class, 'index'])->name('workspaces.index');
    Route::post('/workspaces', [AccountWorkspaceController::class, 'store'])->name('workspaces.store');
    Route::get('/workspaces/{workspace}', [AccountWorkspaceController::class, 'show'])->name('workspaces.show');
    Route::put('/workspaces/{workspace}', [AccountWorkspaceController::class, 'update'])->name('workspaces.update');
    Route::post('/workspaces/{workspace}/invite', [AccountWorkspaceController::class, 'invite'])->name('workspaces.invite');
    Route::put('/workspaces/{workspace}/members/{member}', [AccountWorkspaceController::class, 'updateMember'])->name('workspaces.members.update');
    Route::delete('/workspaces/{workspace}/members/{member}', [AccountWorkspaceController::class, 'removeMember'])->name('workspaces.members.destroy');

    Route::get('/brand-templates', [AccountBrandTemplateController::class, 'index'])->name('brand-templates.index');
    Route::post('/brand-templates', [AccountBrandTemplateController::class, 'store'])->name('brand-templates.store');
    Route::delete('/brand-templates/{brandTemplate}', [AccountBrandTemplateController::class, 'destroy'])->name('brand-templates.destroy');

    Route::get('/campaigns', [AccountCampaignController::class, 'index'])->name('campaigns.index');
    Route::post('/campaigns', [AccountCampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{campaign}', [AccountCampaignController::class, 'show'])->name('campaigns.show');
    Route::delete('/campaigns/{campaign}', [AccountCampaignController::class, 'destroy'])->name('campaigns.destroy');

    Route::get('/bulk-qr', [AccountBulkQrController::class, 'index'])->name('bulk-qr.index');
    Route::post('/bulk-qr', [AccountBulkQrController::class, 'store'])->middleware('throttle:10,1')->name('bulk-qr.store');
    Route::get('/bulk-qr/{bulkJob}/download', [AccountBulkQrController::class, 'download'])->name('bulk-qr.download');

    Route::get('/api-keys', [AccountApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [AccountApiKeyController::class, 'store'])->middleware('throttle:10,1')->name('api-keys.store');
    Route::post('/api-keys/{apiKey}/toggle', [AccountApiKeyController::class, 'toggle'])->name('api-keys.toggle');
    Route::delete('/api-keys/{apiKey}', [AccountApiKeyController::class, 'destroy'])->name('api-keys.destroy');

    Route::get('/qr-codes', [AccountDynamicQrController::class, 'index'])->name('qr-codes.index');
    Route::get('/qr-codes/{qrCode}', [AccountDynamicQrController::class, 'show'])->name('qr-codes.show');
    Route::get('/qr-codes/{qrCode}/edit', [AccountDynamicQrController::class, 'edit'])->name('qr-codes.edit');
    Route::put('/qr-codes/{qrCode}', [AccountDynamicQrController::class, 'update'])->name('qr-codes.update');
    Route::delete('/qr-codes/{qrCode}', [AccountDynamicQrController::class, 'destroy'])->name('qr-codes.destroy');
    Route::post('/qr-codes/{qrCode}/pause', [AccountDynamicQrController::class, 'pause'])->name('qr-codes.pause');
    Route::post('/qr-codes/{qrCode}/resume', [AccountDynamicQrController::class, 'resume'])->name('qr-codes.resume');
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
