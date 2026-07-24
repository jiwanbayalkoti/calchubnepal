<?php

use App\Http\Controllers\Admin\AdReportController;
use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\AdvertiserController;
use App\Http\Controllers\Admin\AiPromptController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\CalculatorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeoPageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
|
| All routes below require an authenticated session (`auth`) plus the
| `admin` gate (super-admin/admin role OR the admin.dashboard.view
| permission). Store/update/destroy/datatable endpoints always return
| JSON; `index` actions render Blade views for the AdminLTE panel.
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('calculators/data', [CalculatorController::class, 'data'])->name('calculators.data');
    Route::patch('calculators/{id}/toggle-active', [CalculatorController::class, 'toggleActive'])->name('calculators.toggle-active');
    Route::patch('calculators/{id}/toggle-featured', [CalculatorController::class, 'toggleFeatured'])->name('calculators.toggle-featured');
    Route::resource('calculators', CalculatorController::class)
        ->except(['create', 'edit'])
        ->parameters(['calculators' => 'id']);

    Route::get('categories/data', [CategoryController::class, 'data'])->name('categories.data');
    Route::resource('categories', CategoryController::class)
        ->except(['create', 'edit'])
        ->parameters(['categories' => 'id']);

    Route::get('blog-posts/data', [BlogPostController::class, 'data'])->name('blog-posts.data');
    Route::post('blog-posts/generate-ai', [BlogPostController::class, 'generateWithAi'])->name('blog-posts.generate-ai');
    Route::resource('blog-posts', BlogPostController::class)
        ->except(['create', 'edit'])
        ->parameters(['blog-posts' => 'id']);

    Route::get('advertisements/data', [AdvertisementController::class, 'data'])->name('advertisements.data');
    Route::patch('advertisements/{id}/toggle-active', [AdvertisementController::class, 'toggleActive'])->name('advertisements.toggle-active');
    Route::patch('advertisements/{id}/pause', [AdvertisementController::class, 'pause'])->name('advertisements.pause');
    Route::patch('advertisements/{id}/resume', [AdvertisementController::class, 'resume'])->name('advertisements.resume');
    Route::patch('advertisements/{id}/expire', [AdvertisementController::class, 'expire'])->name('advertisements.expire');
    Route::resource('advertisements', AdvertisementController::class)
        ->except(['create', 'edit'])
        ->parameters(['advertisements' => 'id']);

    Route::get('advertisers/data', [AdvertiserController::class, 'data'])->name('advertisers.data');
    Route::resource('advertisers', AdvertiserController::class)
        ->except(['create', 'edit'])
        ->parameters(['advertisers' => 'id']);

    Route::get('ad-reports', [AdReportController::class, 'index'])->name('ad-reports.index');
    Route::get('ad-reports/data', [AdReportController::class, 'data'])->name('ad-reports.data');
    Route::get('ad-reports/export/excel', [AdReportController::class, 'exportExcel'])->name('ad-reports.export.excel');
    Route::get('ad-reports/export/pdf', [AdReportController::class, 'exportPdf'])->name('ad-reports.export.pdf');

    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::patch('users/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::resource('users', UserController::class)
        ->except(['create', 'edit'])
        ->parameters(['users' => 'id']);

    Route::get('roles/data', [RoleController::class, 'data'])->name('roles.data');
    Route::resource('roles', RoleController::class)
        ->except(['create', 'edit'])
        ->parameters(['roles' => 'id']);

    Route::get('subscription-plans/data', [SubscriptionPlanController::class, 'data'])->name('subscription-plans.data');
    Route::resource('subscription-plans', SubscriptionPlanController::class)
        ->except(['create', 'edit'])
        ->parameters(['subscription-plans' => 'id']);

    Route::get('api-keys/data', [ApiKeyController::class, 'data'])->name('api-keys.data');
    Route::patch('api-keys/{id}/toggle-active', [ApiKeyController::class, 'toggleActive'])->name('api-keys.toggle-active');
    Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::delete('api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');

    Route::get('feedback/data', [FeedbackController::class, 'data'])->name('feedback.data');
    Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('feedback/{id}', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::put('feedback/{id}', [FeedbackController::class, 'update'])->name('feedback.update');
    Route::delete('feedback/{id}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');

    Route::get('contact-messages/data', [ContactMessageController::class, 'data'])->name('contact-messages.data');
    Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('contact-messages/{id}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::put('contact-messages/{id}', [ContactMessageController::class, 'update'])->name('contact-messages.update');
    Route::delete('contact-messages/{id}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'save'])->name('settings.save');

    Route::get('ai-prompts/data', [AiPromptController::class, 'data'])->name('ai-prompts.data');
    Route::resource('ai-prompts', AiPromptController::class)
        ->except(['create', 'edit'])
        ->parameters(['ai-prompts' => 'id']);

    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/page-views-chart', [AnalyticsController::class, 'pageViewsChart'])->name('analytics.page-views-chart');

    Route::get('seo-pages/data', [SeoPageController::class, 'data'])->name('seo-pages.data');
    Route::resource('seo-pages', SeoPageController::class)
        ->except(['create', 'edit'])
        ->parameters(['seo-pages' => 'id']);
});
