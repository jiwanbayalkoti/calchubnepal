<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Cached key/value application settings store, grouped by `group`.
 */
class SettingsService
{
    private const CACHE_PREFIX = 'calc_hub:settings:';

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX.$group.':'.$key;

        return Cache::remember($cacheKey, $this->ttl(), function () use ($group, $key, $default) {
            $setting = Setting::query()->where('group', $group)->where('key', $key)->first();

            return $setting ? $setting->castValue() : $default;
        });
    }

    public function set(string $group, string $key, mixed $value, string $type = 'string', bool $isPublic = false): Setting
    {
        $storedValue = match (true) {
            is_array($value) => json_encode($value),
            is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };

        $setting = Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $storedValue, 'type' => $type, 'is_public' => $isPublic]
        );

        $this->forget($group, $key);

        return $setting;
    }

    /**
     * @return array<string, mixed>
     */
    public function getGroup(string $group): array
    {
        $cacheKey = self::CACHE_PREFIX.'group:'.$group;

        return Cache::remember($cacheKey, $this->ttl(), function () use ($group) {
            return Setting::query()->where('group', $group)->get()
                ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->castValue()])
                ->all();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function getPublic(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'public', $this->ttl(), function () {
            return Setting::query()->where('is_public', true)->get()
                ->mapWithKeys(fn (Setting $setting) => [$setting->group.'.'.$setting->key => $setting->castValue()])
                ->all();
        });
    }

    public function forget(string $group, string $key): void
    {
        Cache::forget(self::CACHE_PREFIX.$group.':'.$key);
        Cache::forget(self::CACHE_PREFIX.'group:'.$group);
        Cache::forget(self::CACHE_PREFIX.'public');
    }

    private function ttl(): int
    {
        return (int) config('calculator_hub.cache.ttl.settings', 3600);
    }
}
