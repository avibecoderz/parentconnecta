<?php

namespace App\Livewire\School\Parent\Payments;

use App\Livewire\School\Parent\ParentPage;
use App\Models\Payment;
use App\Models\Student;
use App\Services\Payments\Exceptions\PaymentInitializationException;
use App\Services\Payments\Exceptions\PaystackRequestException;
use App\Services\Payments\Exceptions\SchoolPaymentSettingsException;
use App\Services\Payments\ParentPaymentInitializer;
use App\Services\Payments\PaystackService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Throwable;

#[Title('Parent Payments')]
class Index extends ParentPage
{
    protected const LAST_PROCESSED_REFERENCE_SESSION_KEY = 'payments.last_processed_reference';

    public function mount(string $slug): void
    {
        parent::mount($slug);

        $reference = trim((string) request()->query('reference', ''));

        if ($reference !== '' && ! $this->wasReferenceAlreadyProcessed($reference)) {
            $this->processReturnedPayment($reference, app(PaystackService::class));
        }
    }

    public function initializePayment(int $paymentId, ParentPaymentInitializer $initializer)
    {
        $school = $this->currentSchool();
        $payment = $this->linkedPaymentsQuery()
            ->whereKey($paymentId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->with('student:id,school_id,first_name,last_name')
            ->firstOrFail();

        $this->authorize('view', $payment);

        try {
            $checkout = $initializer->initialize($payment, $this->parentUser(), $school);
        } catch (PaymentInitializationException|PaystackRequestException|SchoolPaymentSettingsException $exception) {
            session()->flash('error', $exception->getMessage());

            return null;
        } catch (Throwable $exception) {
            report($exception);
            session()->flash('error', 'Something went wrong while preparing your payment. Please try again.');

            return null;
        }

        return redirect()->away($checkout['authorization_url']);
    }

    protected function processReturnedPayment(string $reference, PaystackService $paystack): void
    {
        if (! preg_match('/\A[A-Za-z0-9._-]+\z/', $reference)) {
            session()->flash('error', 'The returned payment reference is invalid.');

            return;
        }

        $school = $this->currentSchool();
        $linkedChildIds = $this->linkedChildIdsQuery()->pluck('students.id')->map(static fn ($id): int => (int) $id)->all();
        $payment = $this->linkedPaymentsQuery()
            ->where('reference', $reference)
            ->first();

        if (! $payment instanceof Payment) {
            session()->flash('error', 'We could not match this payment return to a payment record in your account.');

            return;
        }

        if ($payment->status === 'paid' && $payment->paid_at !== null) {
            $this->rememberProcessedReference($reference);
            session()->flash('status', 'This payment has already been verified successfully.');

            return;
        }

        try {
            $verification = $paystack->verifyTransaction($reference, $school);
        } catch (PaystackRequestException|SchoolPaymentSettingsException $exception) {
            session()->flash('error', $exception->getMessage());

            return;
        } catch (Throwable $exception) {
            report($exception);
            session()->flash('error', 'Something went wrong while verifying your payment. Please refresh and try again.');

            return;
        }

        $verifiedReference = trim((string) data_get($verification, 'data.reference', ''));
        $verifiedStatus = data_get($verification, 'data.status');
        $verifiedAmount = (int) data_get($verification, 'data.amount', 0);
        $verifiedCurrency = strtoupper(trim((string) data_get($verification, 'data.currency', '')));

        if ($verifiedReference !== $reference || $verifiedStatus !== 'success') {
            session()->flash('error', 'This payment has not been confirmed by Paystack yet.');

            return;
        }

        $processed = DB::transaction(function () use ($linkedChildIds, $payment, $reference, $school, $verification, $verifiedAmount, $verifiedCurrency): bool {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status === 'paid' && $lockedPayment->paid_at !== null) {
                return true;
            }

            $expectedAmountMinor = (int) round((float) $lockedPayment->balance * 100);
            $expectedCurrency = strtoupper(trim((string) ($lockedPayment->currency ?? 'NGN')));

            if (
                (int) $lockedPayment->school_id !== (int) $school->id
                || $lockedPayment->reference !== $reference
                || ! in_array((int) $lockedPayment->student_id, $linkedChildIds, true)
            ) {
                return false;
            }

            if ($verifiedAmount !== $expectedAmountMinor || $verifiedCurrency !== $expectedCurrency) {
                return false;
            }

            $lockedPayment->forceFill([
                'amount_paid' => $lockedPayment->amount_due,
                'balance' => 0,
                'status' => 'paid',
                'payment_method' => $this->resolvedPaymentMethod($verification),
                'paid_at' => $this->resolvedPaidAt($verification),
            ])->save();

            return true;
        });

        if (! $processed) {
            session()->flash('error', 'The verified transaction no longer matches the current payment record.');

            return;
        }

        $this->rememberProcessedReference($reference);
        session()->flash('status', 'Your payment was verified successfully.');
    }

    protected function wasReferenceAlreadyProcessed(string $reference): bool
    {
        return session(static::LAST_PROCESSED_REFERENCE_SESSION_KEY) === $reference;
    }

    protected function rememberProcessedReference(string $reference): void
    {
        session()->put(static::LAST_PROCESSED_REFERENCE_SESSION_KEY, $reference);
    }

    /**
     * @param  array<string, mixed>  $verification
     */
    protected function resolvedPaymentMethod(array $verification): string
    {
        $channel = trim((string) data_get($verification, 'data.channel', ''));

        return $channel !== '' ? strtolower($channel) : 'paystack';
    }

    /**
     * @param  array<string, mixed>  $verification
     */
    protected function resolvedPaidAt(array $verification): Carbon
    {
        foreach ([data_get($verification, 'data.paid_at'), data_get($verification, 'data.transaction_date')] as $candidate) {
            if (! is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            try {
                return Carbon::parse($candidate);
            } catch (\Throwable) {
                continue;
            }
        }

        return now();
    }

    public function render(PaystackService $paystack): View
    {
        $school = $this->currentSchool();
        $this->authorize('viewAny', [Student::class, $school]);
        $this->authorize('viewAny', [Payment::class, $school]);

        $paymentGateway = null;
        $paymentGatewayError = null;

        try {
            $paymentGateway = $paystack->frontendConfig($school);
        } catch (SchoolPaymentSettingsException $exception) {
            $paymentGatewayError = $exception->getMessage();
        }

        $children = $this->linkedChildrenQuery()
            ->with([
                'schoolClass:id,name,section',
                'payments' => fn ($query) => $query
                    ->where('school_id', $school->id)
                    ->orderByDesc('paid_at')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id'),
            ])
            ->orderBy('students.last_name')
            ->orderBy('students.first_name')
            ->get([
                'students.id',
                'students.school_id',
                'students.school_class_id',
                'students.first_name',
                'students.last_name',
                'students.admission_number',
            ])
            ->each(function ($child): void {
                $child->setRelation(
                    'openPayments',
                    $child->payments
                        ->whereIn('status', ['unpaid', 'partial'])
                        ->sortByDesc('created_at')
                        ->values(),
                );

                $child->setRelation(
                    'verifiedPayments',
                    $child->payments
                        ->where('status', 'paid')
                        ->filter(fn ($payment) => $payment->paid_at !== null)
                        ->sortByDesc('paid_at')
                        ->values(),
                );
            });

        return view('livewire.school.parent.payments.index', [
            'school' => $school,
            'children' => $children,
            'metrics' => [
                [
                    'label' => 'Linked children',
                    'value' => number_format((clone $this->linkedChildrenQuery())->count()),
                    'hint' => 'Students linked to your parent account',
                ],
                [
                    'label' => 'Outstanding records',
                    'value' => number_format((clone $this->linkedPaymentsQuery())->whereIn('status', ['unpaid', 'partial'])->count()),
                    'hint' => 'Open balances across your linked children',
                ],
                [
                    'label' => 'Outstanding balance',
                    'value' => 'NGN '.number_format((float) (clone $this->linkedPaymentsQuery())->whereIn('status', ['unpaid', 'partial'])->sum('balance'), 2),
                    'hint' => 'Total balance remaining across all linked children',
                ],
                [
                    'label' => 'Paid records',
                    'value' => number_format((clone $this->linkedPaymentsQuery())->where('status', 'paid')->whereNotNull('paid_at')->count()),
                    'hint' => 'Verified payment records already fully settled',
                ],
            ],
            'recentPaidPayments' => $this->linkedPaymentsQuery()
                ->with([
                    'student:id,first_name,last_name,admission_number,school_class_id',
                    'student.schoolClass:id,name,section',
                ])
                ->where('status', 'paid')
                ->whereNotNull('paid_at')
                ->orderByDesc('paid_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
            'paymentGateway' => $paymentGateway,
            'paymentGatewayError' => $paymentGatewayError,
        ])->layout('layouts.school.parent');
    }
}
