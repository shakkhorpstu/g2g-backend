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
        Schema::create('psw_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psw_id')->constrained('psws')->onDelete('cascade');
            $table->timestamps();

            $table->unique('psw_id'); // One profile per psw
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psw_profiles');
    }
};
