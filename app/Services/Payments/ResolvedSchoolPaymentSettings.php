<?php

namespace App\Services\Payments;

use App\Models\School;
use App\Models\SchoolPaymentSetting;

final class ResolvedSchoolPaymentSettings
{
    public function __construct(
        public readonly School $school,
        public readonly SchoolPaymentSetting $setting,
    ) {}

    public function gatewayName(): string
    {
        return $this->setting->gateway_name;
    }

    public function publicKey(): string
    {
        return (string) $this->setting->paystack_public_key;
    }

    public function secretKey(): string
    {
        return (string) $this->setting->paystack_secret_key;
    }

    public function mode(): string
    {
        return (string) $this->setting->paystack_mode;
    }

    public function isLiveMode(): bool
    {
        return $this->setting->isLiveMode();
    }

    public function isTestMode(): bool
    {
        return $this->setting->isTestMode();
    }

    public function merchantName(): ?string
    {
        return $this->setting->merchant_name;
    }

    public function merchantEmail(): ?string
    {
        return $this->setting->merchant_email;
    }

    public function merchantPhone(): ?string
    {
        return $this->setting->merchant_phone;
    }

    /**
     * Safe, non-secret payload for logs or admin/debug views.
     *
     * @return array<string, mixed>
     */
    public function toSafeArray(): array
    {
        return [
            'school_id' => $this->school->id,
            'school_slug' => $this->school->slug,
            'gateway_name' => $this->gatewayName(),
            'paystack_mode' => $this->mode(),
            'is_active' => (bool) $this->setting->is_active,
            'merchant_name' => $this->merchantName(),
            'merchant_email' => $this->merchantEmail(),
            'merchant_phone' => $this->merchantPhone(),
        ];
    }
}
