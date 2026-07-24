<?php

namespace App\Contracts\Qr;

use App\DTOs\Qr\QrGenerateOptions;
use App\DTOs\Qr\QrGenerateResult;

interface QrCodeGeneratorInterface
{
    public function generate(QrGenerateOptions $options): QrGenerateResult;
}
