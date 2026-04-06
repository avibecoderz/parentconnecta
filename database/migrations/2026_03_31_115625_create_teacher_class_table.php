<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')
                ->constrained('schools')
                ->cascadeOnDelete();
            $table->foreignId('school_class_id')
                ->constrained('school_classes')
                ->cascadeOnDelete();
            $table->foreignId('teacher_user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'school_class_id', 'teacher_user_id']);
            $table->index(['school_id', 'teacher_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_class');
    }
};
