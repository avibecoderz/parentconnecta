<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Services\Payments\Exceptions\PaystackRequestException;
use App\Services\Payments\Exceptions\SchoolPaymentSettingsException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaystackWebhookHandler
{
    public function __construct(
        protected PaystackService $paystack,
    ) {}

    /**
     * @return array{status: string, message: string, http_status: int}
     */
    public function handle(string $rawPayload, string $signature): array
    {
        $payload = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            Log::warning('Paystack webhook received an invalid JSON payload.');

            return $this->invalidPayload();
        }

        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? null;

        if (! is_string($event) || $event === '' || ! is_array($data)) {
            Log::warning('Paystack webhook payload is missing required fields.', [
                'payload' => $this->safePayloadContext($payload),
            ]);

            return $this->invalidPayload();
        }

        if ($event !== 'charge.success') {
            Log::info('Ignoring unsupported Paystack webhook event.', [
                'event' => $event,
            ]);

            return $this->ignored();
        }

        $reference = trim((string) ($data['reference'] ?? ''));

        if ($reference === '' || preg_match('/\A[A-Za-z0-9._-]+\z/', $reference) !== 1) {
            Log::warning('Paystack webhook contained an invalid payment reference.', [
                'event' => $event,
                'payload' => $this->safePayloadContext($payload),
            ]);

            return $this->invalidPayload();
        }

        $payment = Payment::query()
            ->with('school:id,slug')
            ->where('reference', $reference)
            ->first();

        if (! $payment instanceof Payment) {
            Log::warning('Paystack webhook reference did not match any payment record.', [
                'event' => $event,
                'reference' => $reference,
            ]);

            return $this->ignored();
        }

        $school = $payment->school;

        if ($school === null) {
            Log::error('Paystack webhook payment is missing its school relationship.', [
                'payment_id' => $payment->id,
                'school_id' => $payment->school_id,
                'reference' => $reference,
            ]);

            return $this->result('error', 'Webhook processing failed.', 500);
        }

        try {
            $settings = $this->paystack->settingsForSchool($school);
        } catch (SchoolPaymentSettingsException $exception) {
            Log::error('Paystack webhook could not resolve school payment settings.', [
                'payment_id' => $payment->id,
                'school_id' => $payment->school_id,
                'reference' => $reference,
                'error' => $exception->getMessage(),
            ]);

            return $this->result('error', 'Webhook processing failed.', 500);
        }

        if (! $this->paystack->hasValidSignature($rawPayload, $signature, $settings)) {
            Log::warning('Paystack webhook signature verification failed.', [
                'payment_id' => $payment->id,
                'school_id' => $payment->school_id,
                'reference' => $reference,
            ]);

            return $this->result('unauthorized', 'Unauthorized webhook.', 401);
        }

        try {
            $verification = $this->paystack->verifyWithSettings($settings, $reference);
        } catch (PaystackRequestException $exception) {
            Log::error('Paystack webhook verification request failed.', [
                'payment_id' => $payment->id,
                'school_id' => $payment->school_id,
                'reference' => $reference,
                'error' => $exception->getMessage(),
            ]);

            return $this->result('error', 'Webhook processing failed.', 500);
        }

        return DB::transaction(function () use ($payment, $reference, $verification): array {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $lockedPayment->school_id !== (int) $payment->school_id || $lockedPayment->reference !== $reference) {
                Log::warning('Paystack webhook payment context changed before processing.', [
                    'payment_id' => $lockedPayment->id,
                    'school_id' => $lockedPayment->school_id,
                    'reference' => $reference,
                ]);

                return $this->ignored();
            }

            if ($lockedPayment->status === 'paid' && $lockedPayment->paid_at !== null) {
                return $this->result('ok', 'Webhook processed.', 200);
            }

            $verifiedReference = data_get($verification, 'data.reference');
            $verifiedStatus = data_get($verification, 'data.status');
            $verifiedAmount = data_get($verification, 'data.amount');
            $verifiedCurrency = data_get($verification, 'data.currency');

            if ($verifiedReference !== $reference || $verifiedStatus !== 'success') {
                Log::warning('Paystack webhook verification did not confirm a successful transaction.', [
                    'payment_id' => $lockedPayment->id,
                    'school_id' => $lockedPayment->school_id,
                    'reference' => $reference,
                    'verified_reference' => $verifiedReference,
                    'verified_status' => $verifiedStatus,
                ]);

                return $this->ignored();
            }

            $expectedAmountMinor = (int) round((float) $lockedPayment->balance * 100);
            $normalizedCurrency = strtoupper(trim((string) ($lockedPayment->currency ?? 'NGN')));
            $verifiedChannel = data_get($verification, 'data.channel');

            if ((int) $verifiedAmount !== $expectedAmountMinor || strtoupper((string) $verifiedCurrency) !== $normalizedCurrency) {
                Log::error('Paystack webhook verification did not match the expected payment amount or currency.', [
                    'payment_id' => $lockedPayment->id,
                    'school_id' => $lockedPayment->school_id,
                    'reference' => $reference,
                    'expected_amount_minor' => $expectedAmountMinor,
                    'verified_amount_minor' => $verifiedAmount,
                    'expected_currency' => $normalizedCurrency,
                    'verified_currency' => $verifiedCurrency,
                ]);

                return $this->ignored();
            }

            $paidAt = $this->resolvePaidAt(
                data_get($verification, 'data.paid_at'),
                data_get($verification, 'data.transaction_date'),
            );

            $lockedPayment->forceFill([
                'amount_paid' => $lockedPayment->amount_due,
                'balance' => 0,
                'status' => 'paid',
                'payment_method' => is_string($verifiedChannel) && trim($verifiedChannel) !== '' ? strtolower($verifiedChannel) : 'paystack',
                'paid_at' => $paidAt,
            ])->save();

            Log::info('Paystack webhook marked payment as paid.', [
                'payment_id' => $lockedPayment->id,
                'school_id' => $lockedPayment->school_id,
                'reference' => $reference,
            ]);

            return $this->result('ok', 'Webhook processed.', 200);
        });
    }

    protected function resolvePaidAt(mixed ...$candidates): Carbon
    {
        foreach ($candidates as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            try {
                return Carbon::parse($candidate);
            } catch (Throwable) {
                continue;
            }
        }

        return now();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function safePayloadContext(array $payload): array
    {
        return [
            'event' => $payload['event'] ?? null,
            'reference' => data_get($payload, 'data.reference'),
        ];
    }

    /**
     * @return array{status: string, message: string, http_status: int}
     */
    protected function invalidPayload(): array
    {
        return $this->result('ignored', 'Invalid webhook payload.', 400);
    }

    /**
     * @return array{status: string, message: string, http_status: int}
     */
    protected function ignored(): array
    {
        return $this->result('ignored', 'Webhook ignored.', 200);
    }

    /**
     * @return array{status: string, message: string, http_status: int}
     */
    protected function result(string $status, string $message, int $httpStatus): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'http_status' => $httpStatus,
        ];
    }
}
