<?php

namespace App\Models;

use App\Models\Concerns\EnforcesSchoolTenancy;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class SchoolPaymentSetting extends Model
{
    use EnforcesSchoolTenancy;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'gateway_name',
        'paystack_public_key',
        'paystack_secret_key',
        'paystack_mode',
        'is_active',
        'merchant_name',
        'merchant_email',
        'merchant_phone',
        'gateway_metadata',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'paystack_secret_key',
        'gateway_metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'gateway_metadata' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getPaystackSecretKeyAttribute(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }

    public function setPaystackSecretKeyAttribute(?string $value): void
    {
        $value = is_string($value) ? trim($value) : null;

        $this->attributes['paystack_secret_key'] = $value !== null && $value !== ''
            ? Crypt::encryptString($value)
            : null;
    }

    public function isPaystack(): bool
    {
        return $this->gateway_name === 'paystack';
    }

    public function isLiveMode(): bool
    {
        return $this->paystack_mode === 'live';
    }

    public function isTestMode(): bool
    {
        return $this->paystack_mode === 'test';
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && filled($this->gateway_name)
            && filled($this->paystack_public_key)
            && filled($this->paystack_secret_key);
    }
}
