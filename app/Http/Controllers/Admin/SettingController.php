<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Activity\ActivityLogService;
use App\Services\Settings\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
}
