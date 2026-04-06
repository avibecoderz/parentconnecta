<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the primary school this user belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the classes this user teaches.
     */
    public function teachingClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_class', 'teacher_user_id', 'school_class_id')
            ->withPivot(['school_id', 'is_primary', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Get the students linked to this user as a parent.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_user_id', 'student_id')
            ->withPivot(['school_id', 'relationship_type', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Get all results this teacher is linked to.
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'teacher_user_id');
    }

    /**
     * Get all payments this parent is linked to.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_user_id');
    }

    /**
     * Determine whether the user account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Determine whether the user belongs to the given school.
     */
    public function belongsToSchool(School $school): bool
    {
        return (int) $this->school_id === (int) $school->id;
    }

    /**
     * Determine whether the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Determine whether the user is a school admin.
     */
    public function isSchoolAdmin(): bool
    {
        return $this->hasRole('school_admin');
    }

    /**
     * Determine whether the user is a teacher.
     */
    public function isTeacher(): bool
    {
        return $this->hasRole('teacher');
    }

    /**
     * Determine whether the user is a parent.
     */
    public function isParent(): bool
    {
        return $this->hasRole('parent');
    }

    /**
     * Determine whether the teacher is assigned to the given class.
     */
    public function isAssignedToClass(SchoolClass $schoolClass): bool
    {
        if (! $this->isTeacher() || ! $this->belongsToSchool($schoolClass->school)) {
            return false;
        }

        return $this->teachingClasses()
            ->where('school_classes.id', $schoolClass->id)
            ->wherePivot('school_id', $schoolClass->school_id)
            ->exists();
    }

    /**
     * Determine whether the parent is linked to the given student.
     */
    public function isLinkedToStudent(Student $student): bool
    {
        if (! $this->isParent() || (int) $this->school_id !== (int) $student->school_id) {
            return false;
        }

        return $this->children()
            ->where('students.id', $student->id)
            ->wherePivot('school_id', $student->school_id)
            ->exists();
    }
}
