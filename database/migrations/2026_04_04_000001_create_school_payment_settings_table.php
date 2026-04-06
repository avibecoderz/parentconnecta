<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_payment_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('gateway_name', 50);
            $table->string('paystack_public_key')->nullable();
            $table->text('paystack_secret_key')->nullable();
            $table->string('paystack_mode', 20)->default('test');
            $table->boolean('is_active')->default(false);
            $table->string('merchant_name')->nullable();
            $table->string('merchant_email')->nullable();
            $table->string('merchant_phone')->nullable();
            $table->longText('gateway_metadata')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'gateway_name'], 'school_payment_settings_school_gateway_unique');
            $table->index(['school_id', 'is_active'], 'school_payment_settings_school_active_index');
            $table->index(['gateway_name', 'paystack_mode'], 'school_payment_settings_gateway_mode_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_payment_settings');
    }
};
