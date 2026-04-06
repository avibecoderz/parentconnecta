<?php

namespace App\Models;

use App\Models\Concerns\EnforcesSchoolTenancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use EnforcesSchoolTenancy, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'section',
        'code',
        'capacity',
        'status',
    ];

    /**
     * Get the school that owns the class.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the students currently assigned to the class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the teachers assigned to the class.
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_class', 'school_class_id', 'teacher_user_id')
            ->withPivot(['school_id', 'is_primary', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Get the results recorded for this class.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
