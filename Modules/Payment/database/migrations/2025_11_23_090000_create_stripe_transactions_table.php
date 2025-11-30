<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stripe_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_type', 100); // e.g., App\\Models\\User or Modules\\Core\\Models\\Psw
            $table->string('stripe_payment_intent_id', 100)->index();
            $table->string('stripe_charge_id', 100)->nullable()->index();
            $table->string('payment_method_id', 100)->nullable();
            $table->integer('amount'); // stored in smallest currency unit
            $table->string('currency', 10);
            $table->string('status', 50);
            $table->string('description')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'user_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_transactions');
    }
};
