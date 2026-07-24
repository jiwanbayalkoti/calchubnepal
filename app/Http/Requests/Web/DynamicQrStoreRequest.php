<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class DynamicQrStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $input = $this->input('input', []);
        if (! is_array($input)) {
            $input = [];
        }

        $destination = $this->input('destination_url')
            ?: ($input['url'] ?? $input['destination_url'] ?? null);

        $this->merge([
            'destination_url' => $destination,
            'title' => $this->input('title'),
            'password' => $this->input('password'),
            'expires_at' => $this->input('expires_at') ?: null,
            'size' => (int) $this->input('size', 256),
            'margin' => (int) $this->input('margin', 10),
            'foreground' => $this->input('foreground', '#0B6E4F'),
            'background' => $this->input('background', '#FFFFFF'),
            'error_correction' => $this->input('error_correction', 'M'),
            'module_style' => $this->input('module_style', 'square'),
            'eye_style' => $this->input('eye_style', 'square'),
            'frame_style' => $this->input('frame_style', 'none'),
            'frame_label' => $this->input('frame_label', ''),
            'logo_token' => $this->input('logo_token'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'destination_url' => ['required', 'string', 'max:2000'],
            'title' => ['nullable', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:4', 'max:100'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'size' => ['nullable', 'integer', 'in:128,256,512,1024'],
            'margin' => ['nullable', 'integer', 'min:0', 'max:64'],
            'foreground' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'background' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'error_correction' => ['nullable', 'string', 'in:L,M,Q,H'],
            'module_style' => ['nullable', 'string', 'max:32'],
            'eye_style' => ['nullable', 'string', 'max:32'],
            'frame_style' => ['nullable', 'string', 'max:32'],
            'frame_label' => ['nullable', 'string', 'max:40'],
            'logo_token' => ['nullable', 'uuid'],
            'logo' => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->only([
            'destination_url',
            'title',
            'password',
            'expires_at',
            'size',
            'margin',
            'foreground',
            'background',
            'error_correction',
            'module_style',
            'eye_style',
            'frame_style',
            'frame_label',
            'logo_token',
            'input',
        ]);
    }
}
