<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'status',
        'plan',
        'timezone',
        'current_academic_year',
        'current_term',
    ];

    /**
     * Scope only active schools.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Determine whether the school can serve tenant traffic.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function currentAcademicYear(): string
    {
        if (is_string($this->current_academic_year) && $this->current_academic_year !== '') {
            return $this->current_academic_year;
        }

        $currentYear = (int) now()->format('Y');

        return $currentYear.'/'.($currentYear + 1);
    }

    public function currentTerm(): string
    {
        return in_array($this->current_term, ['first', 'second', 'third'], true)
            ? $this->current_term
            : 'first';
    }

    public function currentTermLabel(): string
    {
        return ucfirst($this->currentTerm());
    }

    /**
     * Get all users assigned to the school.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all classes defined for the school.
     */
    public function schoolClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Get all students enrolled under the school.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get all results recorded for the school.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get all payments recorded for the school.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the primary payment settings record for the school.
     */
    public function paymentSetting(): HasOne
    {
        return $this->hasOne(SchoolPaymentSetting::class);
    }

    /**
     * Get the active payment settings record for the school.
     */
    public function activePaymentSetting(): HasOne
    {
        return $this->hasOne(SchoolPaymentSetting::class)
            ->where('is_active', true);
    }
}
