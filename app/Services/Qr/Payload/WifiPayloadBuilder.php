<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class WifiPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Wifi;
    }

    public function build(array $input): string
    {
        $ssid = $this->requireNonEmpty($this->string($input, 'ssid'), 'WiFi SSID');
        $password = $this->string($input, 'password');
        $encryption = strtoupper($this->string($input, 'encryption', 'WPA'));
        if (! in_array($encryption, ['WPA', 'WEP', 'NOPASS'], true)) {
            $encryption = 'WPA';
        }
        $hidden = filter_var($input['hidden'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';

        $ssidEsc = $this->escape($ssid);
        $passEsc = $this->escape($password);

        if ($encryption === 'NOPASS') {
            return "WIFI:T:nopass;S:{$ssidEsc};H:{$hidden};;";
        }

        return "WIFI:T:{$encryption};S:{$ssidEsc};P:{$passEsc};H:{$hidden};;";
    }

    protected function escape(string $value): string
    {
        return str_replace(['\\', ';', ',', ':', '"'], ['\\\\', '\\;', '\\,', '\\:', '\\"'], $value);
    }

    public function rules(): array
    {
        return [
            'input.ssid' => ['required', 'string', 'max:64'],
            'input.password' => ['nullable', 'string', 'max:128'],
            'input.encryption' => ['nullable', 'string', 'in:WPA,WEP,NOPASS'],
            'input.hidden' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.ssid' => 'WiFi name (SSID)',
            'input.password' => 'WiFi password',
            'input.encryption' => 'encryption',
            'input.hidden' => 'hidden network',
        ];
    }
}
