<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('current_academic_year', 9)->nullable()->after('timezone');
            $table->enum('current_term', ['first', 'second', 'third'])->nullable()->after('current_academic_year');
        });

        $currentYear = (int) now()->format('Y');

        DB::table('schools')->update([
            'current_academic_year' => $currentYear.'/'.($currentYear + 1),
            'current_term' => 'first',
        ]);
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['current_academic_year', 'current_term']);
        });
    }
};
