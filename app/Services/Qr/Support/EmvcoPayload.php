<?php

namespace App\Services\Qr\Support;

/**
 * EMVCo Merchant Presented Mode helpers (Nepal QR / Fonepay-style).
 */
class EmvcoPayload
{
    public static function tlv(string $id, string $value): string
    {
        $len = strlen($value);
        if ($len > 99) {
            throw new \InvalidArgumentException("EMVCo field {$id} is too long.");
        }

        return $id.str_pad((string) $len, 2, '0', STR_PAD_LEFT).$value;
    }

    public static function crc16Ccitt(string $payload): string
    {
        $crc = 0xFFFF;
        $length = strlen($payload);
        for ($i = 0; $i < $length; $i++) {
            $crc ^= (ord($payload[$i]) << 8);
            for ($bit = 0; $bit < 8; $bit++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Build a static merchant-presented EMVCo string (currency NPR by default).
     *
     * @param  array{merchant_id: string, merchant_name: string, city?: string, amount?: string, guid?: string, mcc?: string}  $data
     */
    public static function buildNepalStatic(array $data): string
    {
        $guid = $data['guid'] ?? 'np.com.fonepay';
        $merchantId = $data['merchant_id'];
        $name = mb_substr($data['merchant_name'], 0, 25);
        $city = mb_substr($data['city'] ?? 'Kathmandu', 0, 15);
        $mcc = $data['mcc'] ?? '0000';
        $amount = $data['amount'] ?? '';

        $mai = self::tlv('00', $guid).self::tlv('01', $merchantId);

        $parts = [
            self::tlv('00', '01'),
            self::tlv('01', '11'),
            self::tlv('26', $mai),
            self::tlv('52', $mcc),
            self::tlv('53', '524'),
        ];

        if ($amount !== '' && is_numeric($amount)) {
            $parts[] = self::tlv('54', number_format((float) $amount, 2, '.', ''));
        }

        $parts[] = self::tlv('58', 'NP');
        $parts[] = self::tlv('59', $name);
        $parts[] = self::tlv('60', $city);

        $withoutCrc = implode('', $parts).'6304';

        return $withoutCrc.self::crc16Ccitt($withoutCrc);
    }
}
