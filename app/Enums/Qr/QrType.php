<?php

namespace App\Enums\Qr;

enum QrType: string
{
    case Url = 'url';
    case Text = 'text';
    case Email = 'email';
    case Phone = 'phone';
    case Sms = 'sms';
    case WhatsApp = 'whatsapp';
    case Wifi = 'wifi';
    case Maps = 'maps';
    case Location = 'location';
    case Vcard = 'vcard';
    case Event = 'event';
    case Social = 'social';
    case Bank = 'bank';
    // Phase 3 — popular extensions
    case Telegram = 'telegram';
    case Viber = 'viber';
    case Messenger = 'messenger';
    case Mecard = 'mecard';
    case App = 'app';
    case Pdf = 'pdf';
    case Image = 'image';
    case Crypto = 'crypto';
    case Esewa = 'esewa';
    case Khalti = 'khalti';
    case Upi = 'upi';
    case NepalQr = 'nepal_qr';
    case Calendar = 'calendar';
    case Meeting = 'meeting';
    case Music = 'music';
    case Review = 'review';
    case Coupon = 'coupon';
    case MultiUrl = 'multi_url';

    /**
     * @return list<self>
     */
    public static function phaseOne(): array
    {
        return [
            self::Url,
            self::Text,
            self::Email,
            self::Phone,
            self::Sms,
            self::WhatsApp,
        ];
    }

    /**
     * @return list<self>
     */
    public static function phaseTwo(): array
    {
        return [
            self::Wifi,
            self::Maps,
            self::Location,
            self::Vcard,
            self::Event,
            self::Social,
            self::Bank,
        ];
    }

    /**
     * @return list<self>
     */
    public static function phaseThree(): array
    {
        return [
            self::Telegram,
            self::Viber,
            self::Messenger,
            self::Mecard,
            self::App,
            self::Pdf,
            self::Image,
            self::Crypto,
            self::Esewa,
            self::Khalti,
            self::Upi,
            self::NepalQr,
            self::Calendar,
            self::Meeting,
            self::Music,
            self::Review,
            self::Coupon,
            self::MultiUrl,
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Url => 'Website URL',
            self::Text => 'Plain Text',
            self::Email => 'Email',
            self::Phone => 'Phone Number',
            self::Sms => 'SMS',
            self::WhatsApp => 'WhatsApp',
            self::Wifi => 'WiFi',
            self::Maps => 'Map / Directions',
            self::Location => 'Location (Geo)',
            self::Vcard => 'vCard Contact',
            self::Event => 'Event (iCal)',
            self::Social => 'Social Media',
            self::Bank => 'Bank Details',
            self::Telegram => 'Telegram',
            self::Viber => 'Viber',
            self::Messenger => 'Messenger',
            self::Mecard => 'MeCard Contact',
            self::App => 'App Download',
            self::Pdf => 'PDF / File',
            self::Image => 'Image Link',
            self::Crypto => 'Crypto Wallet',
            self::Esewa => 'eSewa',
            self::Khalti => 'Khalti',
            self::Upi => 'UPI Payment',
            self::NepalQr => 'Nepal QR (EMVCo)',
            self::Calendar => 'Google Calendar',
            self::Meeting => 'Video Meeting',
            self::Music => 'Music / Playlist',
            self::Review => 'Google Review',
            self::Coupon => 'Coupon / Promo',
            self::MultiUrl => 'Multi URL',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Url => 'bi-link-45deg',
            self::Text => 'bi-fonts',
            self::Email => 'bi-envelope',
            self::Phone => 'bi-telephone',
            self::Sms => 'bi-chat-dots',
            self::WhatsApp => 'bi-whatsapp',
            self::Wifi => 'bi-wifi',
            self::Maps => 'bi-map',
            self::Location => 'bi-pin-map',
            self::Vcard => 'bi-person-vcard',
            self::Event => 'bi-calendar-event',
            self::Social => 'bi-share',
            self::Bank => 'bi-bank',
            self::Telegram => 'bi-send',
            self::Viber => 'bi-chat-heart',
            self::Messenger => 'bi-chat-quote',
            self::Mecard => 'bi-person-badge',
            self::App => 'bi-phone',
            self::Pdf => 'bi-file-earmark-pdf',
            self::Image => 'bi-image',
            self::Crypto => 'bi-currency-bitcoin',
            self::Esewa => 'bi-wallet2',
            self::Khalti => 'bi-cash-coin',
            self::Upi => 'bi-phone-flip',
            self::NepalQr => 'bi-qr-code-scan',
            self::Calendar => 'bi-calendar2-plus',
            self::Meeting => 'bi-camera-video',
            self::Music => 'bi-music-note-beamed',
            self::Review => 'bi-star',
            self::Coupon => 'bi-ticket-perforated',
            self::MultiUrl => 'bi-collection',
        };
    }

    public function phase(): int
    {
        if (in_array($this, self::phaseThree(), true)) {
            return 3;
        }

        return in_array($this, self::phaseTwo(), true) ? 2 : 1;
    }
}
