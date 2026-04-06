<?php

namespace App\Services\Payments;

use App\Models\School;
use App\Services\Payments\Exceptions\PaystackRequestException;
use App\Services\Payments\Exceptions\SchoolPaymentSettingsException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class PaystackService
{
    protected const GATEWAY_NAME = 'paystack';

    protected const BASE_URL = 'https://api.paystack.co';

    public function __construct(
        protected SchoolPaymentSettingsResolver $settingsResolver,
        protected HttpFactory $http,
    ) {}

    /**
     * Initialize a Paystack transaction for the current tenant school.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function initializeTransaction(array $payload, ?School $school = null): array
    {
        return $this->initializeWithSettings(
            $this->resolveSettings($school),
            $payload,
        );
    }

    /**
     * Initialize a Paystack transaction using an already-resolved school setting.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function initializeWithSettings(ResolvedSchoolPaymentSettings $settings, array $payload): array
    {
        $this->assertValidInitializationPayload($payload);

        try {
            $response = $this->request($settings)
                ->post('/transaction/initialize', $payload);
        } catch (ConnectionException $exception) {
            throw new PaystackRequestException(
                'Unable to reach the payment gateway right now. Please try again in a moment.',
                previous: $exception,
            );
        }

        return $this->parseResponse($response, 'Unable to initialize the Paystack transaction.');
    }

    /**
     * Verify a Paystack transaction by provider reference.
     *
     * @return array<string, mixed>
     */
    public function verifyTransaction(string $reference, ?School $school = null): array
    {
        return $this->verifyWithSettings(
            $this->resolveSettings($school),
            $reference,
        );
    }

    /**
     * Verify a Paystack transaction using an already-resolved school setting.
     *
     * @return array<string, mixed>
     */
    public function verifyWithSettings(ResolvedSchoolPaymentSettings $settings, string $reference): array
    {
        $normalizedReference = trim($reference);

        if ($normalizedReference === '') {
            throw new PaystackRequestException('A Paystack transaction reference is required for verification.');
        }

        try {
            $response = $this->request($settings)
                ->get('/transaction/verify/'.urlencode($normalizedReference));
        } catch (ConnectionException $exception) {
            throw new PaystackRequestException(
                'Unable to verify the payment right now because the payment gateway is unreachable. Please try again shortly.',
                previous: $exception,
            );
        }

        return $this->parseResponse($response, 'Unable to verify the Paystack transaction.');
    }

    /**
     * Return a frontend-safe configuration payload for the current tenant school.
     *
     * @return array<string, mixed>
     */
    public function frontendConfig(?School $school = null): array
    {
        $settings = $this->resolveSettings($school);

        return [
            'public_key' => $settings->publicKey(),
            'gateway' => $settings->gatewayName(),
            'mode' => $settings->mode(),
        ];
    }

    public function settingsForCurrentSchool(): ResolvedSchoolPaymentSettings
    {
        return $this->settingsResolver->resolveCurrent(self::GATEWAY_NAME);
    }

    public function settingsForSchool(School $school): ResolvedSchoolPaymentSettings
    {
        return $this->settingsResolver->resolveForSchool($school, self::GATEWAY_NAME);
    }

    public function hasValidSignature(
        string $rawPayload,
        string $signature,
        ResolvedSchoolPaymentSettings $settings,
    ): bool {
        $normalizedSignature = trim($signature);
        $secretKey = trim($settings->secretKey());

        if ($normalizedSignature === '' || $rawPayload === '' || $secretKey === '') {
            return false;
        }

        $expectedSignature = hash_hmac('sha512', $rawPayload, $secretKey);

        return hash_equals($expectedSignature, $normalizedSignature);
    }

    protected function resolveSettings(?School $school = null): ResolvedSchoolPaymentSettings
    {
        return $school instanceof School
            ? $this->settingsForSchool($school)
            : $this->settingsForCurrentSchool();
    }

    protected function request(ResolvedSchoolPaymentSettings $settings): PendingRequest
    {
        $secretKey = trim($settings->secretKey());

        if ($secretKey === '') {
            throw new SchoolPaymentSettingsException(
                "The Paystack secret key for school [{$settings->school->slug}] is not available."
            );
        }

        return $this->http
            ->baseUrl(self::BASE_URL)
            ->acceptJson()
            ->asJson()
            ->withToken($secretKey)
            ->retry(2, 300, static fn (\Exception $exception): bool => $exception instanceof ConnectionException)
            ->connectTimeout(10)
            ->timeout(30);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function assertValidInitializationPayload(array $payload): void
    {
        $email = $payload['email'] ?? null;
        $amount = $payload['amount'] ?? null;
        $reference = $payload['reference'] ?? null;
        $currency = $payload['currency'] ?? null;

        if (! is_string($email) || trim($email) === '') {
            throw new PaystackRequestException('A customer email address is required to initialize a Paystack transaction.');
        }

        if (! is_int($amount) || $amount <= 0) {
            throw new PaystackRequestException('A valid transaction amount in the smallest currency unit is required.');
        }

        if (! is_string($reference) || trim($reference) === '') {
            throw new PaystackRequestException('A unique transaction reference is required to initialize a Paystack transaction.');
        }

        if (! is_string($currency) || trim($currency) === '') {
            throw new PaystackRequestException('A transaction currency is required to initialize a Paystack transaction.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseResponse(Response $response, string $fallbackMessage): array
    {
        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new PaystackRequestException(
                $this->errorMessageFromResponse($response, $fallbackMessage),
                previous: $exception,
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new PaystackRequestException($fallbackMessage);
        }

        $successful = (bool) ($payload['status'] ?? false);

        if (! $successful) {
            $message = $payload['message'] ?? $fallbackMessage;

            throw new PaystackRequestException(
                is_string($message) && $message !== '' ? $message : $fallbackMessage,
            );
        }

        return $payload;
    }

    protected function errorMessageFromResponse(Response $response, string $fallbackMessage): string
    {
        $payload = $response->json();

        if (is_array($payload) && isset($payload['message']) && is_string($payload['message']) && $payload['message'] !== '') {
            return $payload['message'];
        }

        return $fallbackMessage;
    }
}
