<?php

namespace App\Models;

use App\Models\Concerns\EnforcesSchoolTenancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use EnforcesSchoolTenancy, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'student_id',
        'parent_user_id',
        'reference',
        'payment_type',
        'academic_year',
        'term',
        'amount_due',
        'amount_paid',
        'balance',
        'currency',
        'status',
        'payment_method',
        'paid_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the school that owns the payment.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student the payment was made for.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the parent user who initiated or is linked to the payment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }
}
