<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('psw_availability_days')) {
            return;
        }

        Schema::create('psw_availability_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('psw_profile_id')->index();
            $table->unsignedTinyInteger('day_of_week');
            $table->boolean('is_available')->default(false);
            $table->timestamps();

            $table->unique(['psw_profile_id', 'day_of_week'], 'psw_profile_day_unique');

            if (Schema::hasTable('psw_profiles')) {
                $table->foreign('psw_profile_id')->references('id')->on('psw_profiles')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('psw_availability_days')) {
            Schema::dropIfExists('psw_availability_days');
        }
    }
};
