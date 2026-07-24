<?php

namespace App\Services\Qr;

use App\Contracts\Qr\QrPayloadBuilderInterface;
use App\Enums\Qr\QrType;
use App\Services\Qr\Payload\AppPayloadBuilder;
use App\Services\Qr\Payload\BankPayloadBuilder;
use App\Services\Qr\Payload\CalendarPayloadBuilder;
use App\Services\Qr\Payload\CouponPayloadBuilder;
use App\Services\Qr\Payload\CryptoPayloadBuilder;
use App\Services\Qr\Payload\EmailPayloadBuilder;
use App\Services\Qr\Payload\EsewaPayloadBuilder;
use App\Services\Qr\Payload\EventPayloadBuilder;
use App\Services\Qr\Payload\ImagePayloadBuilder;
use App\Services\Qr\Payload\KhaltiPayloadBuilder;
use App\Services\Qr\Payload\LocationPayloadBuilder;
use App\Services\Qr\Payload\MapsPayloadBuilder;
use App\Services\Qr\Payload\MecardPayloadBuilder;
use App\Services\Qr\Payload\MeetingPayloadBuilder;
use App\Services\Qr\Payload\MessengerPayloadBuilder;
use App\Services\Qr\Payload\MultiUrlPayloadBuilder;
use App\Services\Qr\Payload\MusicPayloadBuilder;
use App\Services\Qr\Payload\NepalQrPayloadBuilder;
use App\Services\Qr\Payload\PdfPayloadBuilder;
use App\Services\Qr\Payload\PhonePayloadBuilder;
use App\Services\Qr\Payload\ReviewPayloadBuilder;
use App\Services\Qr\Payload\SmsPayloadBuilder;
use App\Services\Qr\Payload\SocialPayloadBuilder;
use App\Services\Qr\Payload\TelegramPayloadBuilder;
use App\Services\Qr\Payload\TextPayloadBuilder;
use App\Services\Qr\Payload\UpiPayloadBuilder;
use App\Services\Qr\Payload\UrlPayloadBuilder;
use App\Services\Qr\Payload\VcardPayloadBuilder;
use App\Services\Qr\Payload\ViberPayloadBuilder;
use App\Services\Qr\Payload\WhatsAppPayloadBuilder;
use App\Services\Qr\Payload\WifiPayloadBuilder;
use InvalidArgumentException;

class QrTypeRegistry
{
    /** @var array<string, QrPayloadBuilderInterface> */
    protected array $builders = [];

    public function __construct()
    {
        foreach ([
            new UrlPayloadBuilder,
            new TextPayloadBuilder,
            new EmailPayloadBuilder,
            new PhonePayloadBuilder,
            new SmsPayloadBuilder,
            new WhatsAppPayloadBuilder,
            new WifiPayloadBuilder,
            new MapsPayloadBuilder,
            new LocationPayloadBuilder,
            new VcardPayloadBuilder,
            new EventPayloadBuilder,
            new SocialPayloadBuilder,
            new BankPayloadBuilder,
            new TelegramPayloadBuilder,
            new ViberPayloadBuilder,
            new MessengerPayloadBuilder,
            new MecardPayloadBuilder,
            new AppPayloadBuilder,
            new PdfPayloadBuilder,
            new ImagePayloadBuilder,
            new CryptoPayloadBuilder,
            new EsewaPayloadBuilder,
            new KhaltiPayloadBuilder,
            new UpiPayloadBuilder,
            new NepalQrPayloadBuilder,
            new CalendarPayloadBuilder,
            new MeetingPayloadBuilder,
            new MusicPayloadBuilder,
            new ReviewPayloadBuilder,
            new CouponPayloadBuilder,
            new MultiUrlPayloadBuilder,
        ] as $builder) {
            $this->register($builder);
        }
    }

    public function register(QrPayloadBuilderInterface $builder): void
    {
        $this->builders[$builder->type()->value] = $builder;
    }

    public function get(QrType|string $type): QrPayloadBuilderInterface
    {
        $key = $type instanceof QrType ? $type->value : $type;

        if (! isset($this->builders[$key])) {
            throw new InvalidArgumentException("Unsupported QR type [{$key}].");
        }

        return $this->builders[$key];
    }

    /**
     * @return list<QrPayloadBuilderInterface>
     */
    public function all(): array
    {
        return array_values($this->builders);
    }

    /**
     * @return list<array{value: string, label: string, icon: string, phase: int}>
     */
    public function options(): array
    {
        return array_map(static fn (QrPayloadBuilderInterface $builder) => [
            'value' => $builder->type()->value,
            'label' => $builder->type()->label(),
            'icon' => $builder->type()->icon(),
            'phase' => $builder->type()->phase(),
        ], $this->all());
    }
}
