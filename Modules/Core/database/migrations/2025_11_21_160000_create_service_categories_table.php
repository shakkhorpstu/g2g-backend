<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('base_fare', 10, 2)->nullable();
            $table->decimal('ride_charge', 10, 2)->nullable();
            $table->decimal('time_charge', 10, 2)->nullable();
            $table->decimal('platform_fee', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};