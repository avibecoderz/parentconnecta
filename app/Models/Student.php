<?php

namespace App\Models;

use App\Models\Concerns\EnforcesSchoolTenancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use EnforcesSchoolTenancy, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'school_class_id',
        'admission_number',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'status',
        'admitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admitted_at' => 'date',
        ];
    }

    /**
     * Get the school that owns the student record.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the current class assignment for the student.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * Get the parent users linked to the student.
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_user_id')
            ->withPivot(['school_id', 'relationship_type', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Get all results recorded for the student.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Get all payments linked to the student.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
