<?php

namespace App\Http\Requests\Web;

use App\Enums\Qr\QrStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DynamicQrUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $qr = $this->route('qrCode');

        return $this->user() !== null && $this->user()->can('update', $qr);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'destination_url' => ['sometimes', 'required', 'string', 'max:2000'],
            'password' => ['nullable', 'string', 'min:4', 'max:100'],
            'remove_password' => ['sometimes', 'boolean'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in(array_column(QrStatus::cases(), 'value'))],
        ];
    }
}
