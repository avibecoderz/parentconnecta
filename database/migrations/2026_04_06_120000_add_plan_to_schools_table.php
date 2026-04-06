<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('schools', 'plan')) {
            Schema::table('schools', function (Blueprint $table): void {
                $table->enum('plan', ['free', 'basic', 'premium'])
                    ->default('free')
                    ->after('status');

                $table->index('plan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('schools', 'plan')) {
            Schema::table('schools', function (Blueprint $table): void {
                $table->dropIndex(['plan']);
                $table->dropColumn('plan');
            });
        }
    }
};
