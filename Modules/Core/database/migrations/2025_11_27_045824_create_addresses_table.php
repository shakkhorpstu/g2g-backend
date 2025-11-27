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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship
            $table->string('addressable_type');
            $table->unsignedBigInteger('addressable_id');
            
            // Address information
            $table->string('label')->default('Home')->nullable(); // 'Home', 'Office', 'Cottage', 'Family', etc.
            $table->text('address_line')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            
            // Geolocation
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Default address flag
            $table->boolean('is_default')->default(false);
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['addressable_type', 'addressable_id'], 'addresses_addressable_index');
            $table->index(['addressable_type', 'addressable_id', 'is_default'], 'addresses_default_index');
            $table->index('postal_code');
            $table->index('country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
