<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->default(0)->after('amount');
            $table->decimal('balance', 12, 2)->default(0)->after('amount_paid');
        });

        DB::statement('ALTER TABLE payments CHANGE amount amount_due DECIMAL(12,2) NOT NULL');
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','paid','failed','refunded','partial','unpaid') NOT NULL DEFAULT 'unpaid'");

        DB::table('payments')->update([
            'amount_paid' => DB::raw("CASE WHEN status = 'paid' THEN amount_due ELSE 0 END"),
            'balance' => DB::raw("CASE WHEN status = 'paid' THEN 0 ELSE amount_due END"),
            'status' => DB::raw("CASE WHEN status = 'paid' THEN 'paid' ELSE 'unpaid' END"),
        ]);

        DB::statement("ALTER TABLE payments MODIFY status ENUM('paid','partial','unpaid') NOT NULL DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','paid','failed','refunded','partial','unpaid') NOT NULL DEFAULT 'unpaid'");

        DB::table('payments')->update([
            'status' => DB::raw("CASE WHEN status = 'paid' THEN 'paid' ELSE 'pending' END"),
        ]);

        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending'");
        DB::statement('ALTER TABLE payments CHANGE amount_due amount DECIMAL(12,2) NOT NULL');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'balance']);
        });
    }
};
