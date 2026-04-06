<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\School;
use App\Models\User;
use App\Services\Payments\Exceptions\PaymentInitializationException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ParentPaymentInitializer
{
    public function __construct(
        protected PaystackService $paystack,
    ) {}

    /**
     * Initialize a Paystack checkout for an existing school payment record.
     *
     * @return array{
     *     payment_id: int,
     *     school_id: int,
     *     student_id: int,
     *     reference: string,
     *     amount: float,
     *     amount_minor: int,
     *     currency: string,
     *     public_key: string,
     *     authorization_url: string,
     *     access_code: ?string,
     *     gateway: string,
     *     mode: string
     * }
     */
    public function initialize(Payment $payment, User $parent, School $school): array
    {
        try {
            return Cache::lock("payments:initialize:{$payment->id}", 10)->block(5, function () use ($payment, $parent, $school): array {
                $parentEmail = strtolower(trim((string) $parent->email));
                $payment = Payment::query()
                    ->with('student:id,school_id,first_name,last_name')
                    ->findOrFail($payment->id);

                $student = $payment->student;

                if (! $student) {
                    throw new PaymentInitializationException('The selected payment is not linked to a valid student record.');
                }

                if ((int) $payment->school_id !== (int) $school->id || (int) $student->school_id !== (int) $school->id) {
                    throw new PaymentInitializationException('This payment does not belong to the current school.');
                }

                if (! $parent->isParent() || ! $parent->belongsToSchool($school)) {
                    throw new PaymentInitializationException('Only linked parent accounts can initialize school payments.');
                }

                if (! $parent->isLinkedToStudent($student)) {
                    throw new PaymentInitializationException('You are not allowed to pay for the selected student.');
                }

                if (! in_array($payment->status, ['unpaid', 'partial'], true)) {
                    throw new PaymentInitializationException('Only unpaid or partially paid records can be initialized for payment.');
                }

                if ($parentEmail === '') {
                    throw new PaymentInitializationException('Your parent account must have an email address before payment can be initialized.');
                }

                $balance = round((float) $payment->balance, 2);

                if ($balance <= 0) {
                    throw new PaymentInitializationException('This payment record no longer has an outstanding balance.');
                }

                $settings = $this->paystack->settingsForSchool($school);
                $reference = $this->generateReference();
                $currency = $this->resolveCurrency($payment);
                $amountMinor = (int) round($balance * 100);

                $response = $this->paystack->initializeWithSettings($settings, [
                    'email' => $parentEmail,
                    'amount' => $amountMinor,
                    'currency' => $currency,
                    'reference' => $reference,
                    'callback_url' => route('school.parent.payments.index', [
                        'slug' => $school->slug,
                        'reference' => $reference,
                    ]),
                    'metadata' => [
                        'school_id' => $school->id,
                        'school_slug' => $school->slug,
                        'payment_id' => $payment->id,
                        'student_id' => $student->id,
                        'student_name' => trim($student->first_name.' '.$student->last_name),
                        'parent_user_id' => $parent->id,
                        'payment_type' => $payment->payment_type,
                        'academic_year' => $payment->academic_year,
                        'term' => $payment->term,
                    ],
                ]);

                $data = $response['data'] ?? null;

                if (! is_array($data)) {
                    throw new PaymentInitializationException('Paystack returned an invalid initialization payload.');
                }

                $authorizationUrl = $data['authorization_url'] ?? null;
                $accessCode = $data['access_code'] ?? null;
                $providerReference = $data['reference'] ?? null;

                if (! is_string($authorizationUrl) || $authorizationUrl === '') {
                    throw new PaymentInitializationException('Paystack did not return a checkout URL for this transaction.');
                }

                if (! is_string($providerReference) || $providerReference === '') {
                    throw new PaymentInitializationException('Paystack did not return a transaction reference.');
                }

                if ($providerReference !== $reference) {
                    throw new PaymentInitializationException('The payment gateway returned an unexpected transaction reference.');
                }

                $payment->forceFill([
                    'parent_user_id' => $parent->id,
                    'reference' => $providerReference,
                    'currency' => $currency,
                    'payment_method' => 'paystack',
                ])->save();

                return [
                    'payment_id' => $payment->id,
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'reference' => $providerReference,
                    'amount' => $balance,
                    'amount_minor' => $amountMinor,
                    'currency' => $currency,
                    'public_key' => $settings->publicKey(),
                    'authorization_url' => $authorizationUrl,
                    'access_code' => is_string($accessCode) ? $accessCode : null,
                    'gateway' => $settings->gatewayName(),
                    'mode' => $settings->mode(),
                ];
            });
        } catch (LockTimeoutException $exception) {
            throw new PaymentInitializationException(
                'Another payment initialization is already in progress for this record. Please wait a few seconds and try again.',
                previous: $exception,
            );
        }
    }

    protected function resolveCurrency(Payment $payment): string
    {
        $currency = strtoupper(trim((string) ($payment->currency ?? 'NGN')));

        return $currency !== '' ? $currency : 'NGN';
    }

    protected function generateReference(): string
    {
        do {
            $reference = 'PAY-'.Str::upper(Str::random(10));
        } while (Payment::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
