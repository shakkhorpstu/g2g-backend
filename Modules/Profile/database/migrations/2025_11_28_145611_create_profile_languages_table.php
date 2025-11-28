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
        Schema::create('profile_languages', function (Blueprint $table) {
            $table->id();
            $table->string('languageable_type');
            $table->unsignedBigInteger('languageable_id');
            $table->string('language', 10); // en, bn, etc.
            $table->timestamps();

            $table->index(['languageable_type', 'languageable_id'], 'profile_languages_languageable_index');
            $table->unique(['languageable_type', 'languageable_id'], 'profile_languages_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_languages');
    }
};
