<?php

namespace App\Http\Requests\Web;

use App\Enums\Qr\QrErrorCorrection;
use App\Enums\Qr\QrEyeStyle;
use App\Enums\Qr\QrFrameStyle;
use App\Enums\Qr\QrModuleStyle;
use App\Enums\Qr\QrOutputFormat;
use App\Enums\Qr\QrType;
use App\Services\Qr\QrTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QrGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $input = $this->input('input', []);
        if (is_string($input)) {
            $decoded = json_decode($input, true);
            $input = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($input)) {
            $input = [];
        }

        // Nested fields from FormData: input[ssid] already works; also accept flat JSON.
        foreach ($this->all() as $key => $value) {
            if (str_starts_with($key, 'input.') && ! array_key_exists(substr($key, 6), $input)) {
                $input[substr($key, 6)] = $value;
            }
        }

        if ($this->has('input_hidden')) {
            $input['hidden'] = filter_var($this->input('input_hidden'), FILTER_VALIDATE_BOOLEAN);
        }

        $this->merge([
            'input' => $input,
            'type' => $this->input('type', QrType::Url->value),
            'size' => (int) $this->input('size', 256),
            'margin' => (int) $this->input('margin', 10),
            'logo_size' => (int) $this->input('logo_size', 64),
            'foreground' => $this->input('foreground', '#0B6E4F'),
            'background' => $this->input('background', '#FFFFFF'),
            'error_correction' => $this->input('error_correction', 'M'),
            'format' => $this->input('format', 'png'),
            'module_style' => $this->input('module_style', 'square'),
            'eye_style' => $this->input('eye_style', 'square'),
            'frame_style' => $this->input('frame_style', 'none'),
            'frame_label' => $this->input('frame_label', ''),
            'save_history' => filter_var($this->input('save_history', false), FILTER_VALIDATE_BOOLEAN),
            'logo_token' => $this->input('logo_token'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var QrTypeRegistry $registry */
        $registry = app(QrTypeRegistry::class);
        $type = (string) $this->input('type', QrType::Url->value);

        $rules = [
            'type' => ['required', 'string', Rule::in(array_column($registry->options(), 'value'))],
            'size' => ['required', 'integer', Rule::in([128, 256, 512, 1024])],
            'foreground' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'background' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'margin' => ['required', 'integer', 'min:0', 'max:64'],
            'error_correction' => ['required', 'string', Rule::in(array_column(QrErrorCorrection::cases(), 'value'))],
            'format' => ['nullable', 'string', Rule::in(array_column(QrOutputFormat::cases(), 'value'))],
            'module_style' => ['nullable', 'string', Rule::in(array_column(QrModuleStyle::cases(), 'value'))],
            'eye_style' => ['nullable', 'string', Rule::in(array_column(QrEyeStyle::cases(), 'value'))],
            'frame_style' => ['nullable', 'string', Rule::in(array_column(QrFrameStyle::cases(), 'value'))],
            'frame_label' => ['nullable', 'string', 'max:60'],
            'logo_size' => ['nullable', 'integer', 'min:24', 'max:200'],
            'logo_token' => ['nullable', 'uuid'],
            'logo' => ['nullable', 'image', 'max:2048', 'mimes:png,jpg,jpeg,webp,gif'],
            'save_history' => ['nullable', 'boolean'],
            'input' => ['required', 'array'],
        ];

        try {
            $rules = array_merge($rules, $registry->get($type)->rules());
        } catch (\Throwable) {
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        /** @var QrTypeRegistry $registry */
        $registry = app(QrTypeRegistry::class);
        $type = (string) $this->input('type', QrType::Url->value);

        $attributes = [
            'type' => 'QR type',
            'size' => 'size',
            'foreground' => 'foreground color',
            'background' => 'background color',
            'margin' => 'margin',
            'error_correction' => 'error correction',
            'format' => 'format',
            'module_style' => 'module style',
            'eye_style' => 'eye style',
            'frame_style' => 'frame style',
            'frame_label' => 'frame label',
            'logo' => 'logo',
        ];

        try {
            $attributes = array_merge($attributes, $registry->get($type)->attributes());
        } catch (\Throwable) {
        }

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function qrPayload(): array
    {
        return $this->safe()->only([
            'type',
            'input',
            'size',
            'foreground',
            'background',
            'margin',
            'error_correction',
            'format',
            'module_style',
            'eye_style',
            'frame_style',
            'frame_label',
            'logo_size',
            'logo_token',
            'save_history',
        ]);
    }
}
