<?php

namespace App\Http\Requests\Web;

use App\Enums\VisitingCard\CardTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VisitingCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'include_qr' => filter_var($this->input('include_qr', false), FILTER_VALIDATE_BOOLEAN),
            'template' => $this->input('template', CardTemplate::Classic->value),
            'qr_target' => $this->input('qr_target', 'website'),
            'primary_color' => $this->input('primary_color', '#0B6E4F'),
            'secondary_color' => $this->input('secondary_color', '#F4A259'),
            'text_color' => $this->input('text_color', '#1A1A1A'),
            'background_color' => $this->input('background_color', '#FFFFFF'),
            'format' => strtolower((string) $this->input('format', 'png')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['nullable', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'website' => ['nullable', 'string', 'max:200'],
            'address' => ['nullable', 'string', 'max:200'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'template' => ['required', 'string', Rule::in(array_column(CardTemplate::cases(), 'value'))],
            'primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'text_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'background_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'include_qr' => ['sometimes', 'boolean'],
            'qr_target' => ['nullable', 'string', Rule::in(['website', 'vcard', 'email', 'phone'])],
            'logo_token' => ['nullable', 'string', 'uuid'],
            'logo' => ['nullable', 'file', 'image', 'max:2048', 'mimes:png,jpg,jpeg,webp,gif'],
            'format' => ['nullable', 'string', Rule::in(['png', 'pdf'])],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cardPayload(): array
    {
        return $this->only([
            'full_name',
            'job_title',
            'company',
            'phone',
            'email',
            'website',
            'address',
            'tagline',
            'template',
            'primary_color',
            'secondary_color',
            'text_color',
            'background_color',
            'include_qr',
            'qr_target',
            'logo_token',
        ]);
    }
}
