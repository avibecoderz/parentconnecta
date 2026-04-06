<?php

namespace App\Models;

use App\Models\Concerns\EnforcesSchoolTenancy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use EnforcesSchoolTenancy, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'student_id',
        'school_class_id',
        'teacher_user_id',
        'subject_name',
        'academic_year',
        'term',
        'ca_score',
        'exam_score',
        'total_score',
        'grade',
        'remark',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ca_score' => 'decimal:2',
            'exam_score' => 'decimal:2',
            'total_score' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get the school that owns the result.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the student this result belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class context for the result.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * Get the teacher who recorded or owns the result entry.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }
}
