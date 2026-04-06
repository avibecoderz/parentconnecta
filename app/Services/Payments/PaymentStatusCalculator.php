<?php

namespace App\Services\Payments;

class PaymentStatusCalculator
{
    /**
     * @return array{amount_due: float, amount_paid: float, balance: float, status: string}
     */
    public function fromAmounts(float $amountDue, float $amountPaid): array
    {
        $normalizedAmountDue = round(max($amountDue, 0), 2);
        $normalizedAmountPaid = round(max(min($amountPaid, $normalizedAmountDue), 0), 2);
        $balance = round(max($normalizedAmountDue - $normalizedAmountPaid, 0), 2);

        return [
            'amount_due' => $normalizedAmountDue,
            'amount_paid' => $normalizedAmountPaid,
            'balance' => $balance,
            'status' => $this->statusFor($normalizedAmountDue, $normalizedAmountPaid, $balance),
        ];
    }

    protected function statusFor(float $amountDue, float $amountPaid, float $balance): string
    {
        if ($amountDue === 0.0 || $balance === 0.0) {
            return 'paid';
        }

        if ($amountPaid > 0) {
            return 'partial';
        }

        return 'unpaid';
    }
}
