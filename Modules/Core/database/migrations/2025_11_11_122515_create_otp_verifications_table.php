<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('otpable'); // Creates otpable_type and otpable_id columns
            $table->string('identifier'); // Email or phone number where OTP was sent
            $table->string('otp_code'); // Encrypted OTP code
            $table->enum('type', ['account_verification', 'forgot_password', 'email_update', 'phone_update']);
            $table->enum('status', ['pending', 'verified', 'expired', 'failed'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->timestamps();
        });
        
        // Add indexes separately to handle duplicates
        try {
            Schema::table('otp_verifications', function (Blueprint $table) {
                $table->index(['otpable_type', 'otpable_id']);
                $table->index(['identifier', 'type']);
                $table->index(['status', 'expires_at']);
            });
        } catch (\Exception $e) {
            // Indexes might already exist, ignore the error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
