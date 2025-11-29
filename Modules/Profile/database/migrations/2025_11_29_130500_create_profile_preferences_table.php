<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('profile_preferences')) {
            return;
        }

        Schema::create('profile_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('preference_id');
            $table->morphs('owner'); // owner_id, owner_type
            $table->timestamps();

            $table->foreign('preference_id')->references('id')->on('preferences')->onDelete('cascade');
            $table->unique(['preference_id', 'owner_id', 'owner_type'], 'profile_pref_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_preferences');
    }
};
