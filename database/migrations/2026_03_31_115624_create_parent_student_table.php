<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('parent_user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->enum('relationship_type', ['father', 'mother', 'guardian', 'other'])->default('other');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'parent_user_id']);
            $table->index(['school_id', 'parent_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_student');
    }
};
