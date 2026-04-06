<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('school_class_id')
                ->constrained('school_classes');
            $table->foreignId('teacher_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('subject_name', 100);
            $table->string('academic_year', 9);
            $table->enum('term', ['first', 'second', 'third']);
            $table->decimal('ca_score', 5, 2)->default(0);
            $table->decimal('exam_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->string('grade', 5)->nullable();
            $table->string('remark', 191)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'student_id', 'subject_name', 'academic_year', 'term'], 'results_student_subject_term_unique');
            $table->index(['school_id', 'school_class_id', 'academic_year', 'term'], 'results_class_year_term_index');
            $table->index(['school_id', 'teacher_user_id'], 'results_school_teacher_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
