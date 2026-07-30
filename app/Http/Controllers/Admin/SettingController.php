<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Activity\ActivityLogService;
use App\Services\Settings\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        protected SettingsService $settings,
        protected ActivityLogService $activityLog,
    ) {
    }

    public function index(): View
    {
        $groups = [
            'site' => Setting::query()->group('site')->get(),
            'seo' => Setting::query()->group('seo')->get(),
            'ads' => Setting::query()->group('ads')->get(),
            'ai' => Setting::query()->group('ai')->get(),
            'social' => Setting::query()->group('social')->get(),
        ];

        return view('admin.settings.index', compact('groups'));
    }

    /**
     * Persist an entire settings group in one AJAX call: expects
     * `group` plus `settings[key] => value` (and optional `types[key]`).
     */
    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'max:255'],
            'settings' => ['required', 'array'],
            'types' => ['nullable', 'array'],
            'public' => ['nullable', 'array'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $type = $validated['types'][$key] ?? 'string';
            $isPublic = in_array($key, $validated['public'] ?? [], true);

            $this->settings->set($validated['group'], $key, $value, $type, $isPublic);
        }

        if (in_array($validated['group'], ['ads', 'site'], true)) {
            foreach (['header', 'sidebar', 'footer', 'sticky', 'in_content', 'between_results'] as $position) {
                \Illuminate\Support\Facades\Cache::forget("calc_hub:ads:{$position}");
            }
        }

        $this->activityLog->log('update', 'settings', null, ['group' => $validated['group']]);

        return response()->json(['message' => 'Settings saved successfully.']);
    }

    /**
     * Upload/replace the site logo. Stores the image on the public disk and
     * saves its path as the public `site.logo` setting.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'file', 'mimes:png,jpg,jpeg,webp,svg,gif', 'max:2048'],
        ]);

        $old = $this->settings->get('site', 'logo');

        $path = $request->file('logo')->store('branding', 'public');

        $this->settings->set('site', 'logo', $path, 'string', true);

        $this->deleteStoredLogo($old);

        $this->activityLog->log('update', 'settings', null, ['group' => 'site', 'key' => 'logo']);

        return response()->json([
            'message' => 'Logo uploaded successfully.',
            'url' => '/storage/'.$path,
        ]);
    }

    /**
     * Remove the current site logo (deletes the file and clears the setting).
     */
    public function removeLogo(): JsonResponse
    {
        $old = $this->settings->get('site', 'logo');

        $this->deleteStoredLogo($old);

        $this->settings->set('site', 'logo', '', 'string', true);

        $this->activityLog->log('update', 'settings', null, ['group' => 'site', 'key' => 'logo', 'action' => 'remove']);

        return response()->json(['message' => 'Logo removed successfully.']);
    }

    /**
     * Delete a previously stored logo file from the public disk (ignores
     * empty values and externally hosted absolute URLs).
     */
    protected function deleteStoredLogo(mixed $value): void
    {
        if (! is_string($value) || $value === '' || str_starts_with($value, 'http') || str_starts_with($value, '/')) {
            return;
        }

        if (Storage::disk('public')->exists($value)) {
            Storage::disk('public')->delete($value);
        }
    }
}
