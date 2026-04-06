<?php

namespace App\Services\Payments;

use App\Models\School;
use App\Models\SchoolPaymentSetting;
use App\Services\Payments\Exceptions\SchoolPaymentSettingsException;
use Illuminate\Http\Request;

class SchoolPaymentSettingsResolver
{
    public function __construct(
        protected Request $request,
    ) {}

    public function resolveCurrent(string $gatewayName = 'paystack'): ResolvedSchoolPaymentSettings
    {
        return $this->resolveForSchool($this->currentSchool(), $gatewayName);
    }

    public function resolveForSchool(School $school, string $gatewayName = 'paystack'): ResolvedSchoolPaymentSettings
    {
        $normalizedGatewayName = strtolower(trim($gatewayName));

        $setting = SchoolPaymentSetting::query()
            ->where('school_id', $school->id)
            ->where('gateway_name', $normalizedGatewayName)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (! $setting instanceof SchoolPaymentSetting) {
            throw new SchoolPaymentSettingsException(
                "No active {$normalizedGatewayName} payment settings are configured for school [{$school->slug}]."
            );
        }

        if (! $setting->isUsable()) {
            throw new SchoolPaymentSettingsException(
                "The {$normalizedGatewayName} payment settings for school [{$school->slug}] are incomplete."
            );
        }

        return new ResolvedSchoolPaymentSettings($school, $setting);
    }

    public function currentSchool(): School
    {
        $school = $this->request->attributes->get('currentSchool');

        if (! $school instanceof School) {
            throw new SchoolPaymentSettingsException('No current school is available in the request context.');
        }

        return $school;
    }
}
