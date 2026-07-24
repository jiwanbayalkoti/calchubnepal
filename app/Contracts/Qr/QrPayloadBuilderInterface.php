<?php

namespace App\Contracts\Qr;

use App\Enums\Qr\QrType;

interface QrPayloadBuilderInterface
{
    public function type(): QrType;

    /**
     * @param  array<string, mixed>  $input
     */
    public function build(array $input): string;

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array;

    /**
     * @return array<string, string>
     */
    public function attributes(): array;
}
