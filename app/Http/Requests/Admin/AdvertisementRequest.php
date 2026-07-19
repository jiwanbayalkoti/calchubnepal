<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKb = (int) config('calculator_hub.ads.max_upload_kb', 1024);
        $positions = array_keys(config('calculator_hub.ads.positions', []));

        if ($positions === []) {
            $positions = ['header', 'sidebar', 'footer', 'sticky', 'in_content', 'between_results'];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('advertisements', 'slug')->ignore($this->route('id'))],
            'position' => ['required', Rule::in($positions)],
            'ad_type' => ['required', Rule::in(['adsense', 'banner', 'html', 'affiliate'])],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:'.$maxKb],
            'remove_image' => ['nullable', 'boolean'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'adsense_code' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $maxKb = (int) config('calculator_hub.ads.max_upload_kb', 1024);

        return [
            'image_file.image' => 'The uploaded file must be an image.',
            'image_file.mimes' => 'Allowed formats: JPG, PNG, WEBP, GIF.',
            'image_file.max' => "Image must be {$maxKb} KB or smaller.",
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }

        if ($this->has('remove_image')) {
            $this->merge([
                'remove_image' => filter_var($this->input('remove_image'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }

        if ($this->input('image') === '') {
            $this->merge(['image' => null]);
        }
    }
}
