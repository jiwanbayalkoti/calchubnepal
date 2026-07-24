<?php

namespace App\Services\Qr\Payload;

use App\Enums\Qr\QrType;

class BankPayloadBuilder extends AbstractQrPayloadBuilder
{
    public function type(): QrType
    {
        return QrType::Bank;
    }

    public function build(array $input): string
    {
        $accountName = $this->requireNonEmpty($this->string($input, 'account_name'), 'Account holder name');
        $bankName = $this->requireNonEmpty($this->string($input, 'bank_name'), 'Bank name');
        $accountNumber = $this->requireNonEmpty($this->string($input, 'account_number'), 'Account number');
        $branch = $this->string($input, 'branch');
        $accountType = $this->string($input, 'account_type');
        $swift = $this->string($input, 'swift_code');
        $remarks = $this->string($input, 'remarks');

        $lines = [
            'BANK TRANSFER DETAILS',
            'Account Name: '.$accountName,
            'Bank: '.$bankName,
            'Account Number: '.$accountNumber,
        ];

        if ($branch !== '') {
            $lines[] = 'Branch: '.$branch;
        }
        if ($accountType !== '') {
            $lines[] = 'Account Type: '.$accountType;
        }
        if ($swift !== '') {
            $lines[] = 'SWIFT/BIC: '.strtoupper($swift);
        }
        if ($remarks !== '') {
            $lines[] = 'Remarks: '.$remarks;
        }

        return implode("\n", $lines);
    }

    public function rules(): array
    {
        return [
            'input.account_name' => ['required', 'string', 'max:150'],
            'input.bank_name' => ['required', 'string', 'max:150'],
            'input.account_number' => ['required', 'string', 'max:64'],
            'input.branch' => ['nullable', 'string', 'max:120'],
            'input.account_type' => ['nullable', 'string', 'max:40'],
            'input.swift_code' => ['nullable', 'string', 'max:20'],
            'input.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'input.account_name' => 'account holder name',
            'input.bank_name' => 'bank name',
            'input.account_number' => 'account number',
            'input.branch' => 'branch',
            'input.account_type' => 'account type',
            'input.swift_code' => 'SWIFT / BIC',
            'input.remarks' => 'remarks',
        ];
    }
}
