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
        if (Schema::hasTable('psw_availability_slots')) {
            return;
        }

        Schema::create('psw_availability_slots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('psw_profile_id')->index();
            $table->unsignedBigInteger('availability_day_id')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('slot_duration_minutes')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['availability_day_id', 'start_time', 'end_time'], 'day_start_end_unique');
            $table->index(['psw_profile_id', 'availability_day_id'], 'psw_profile_day_idx');

            if (Schema::hasTable('psw_availability_days')) {
                $table->foreign('availability_day_id')->references('id')->on('psw_availability_days')->onDelete('cascade');
            }
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
        if (Schema::hasTable('psw_availability_slots')) {
            Schema::dropIfExists('psw_availability_slots');
        }
    }
};
