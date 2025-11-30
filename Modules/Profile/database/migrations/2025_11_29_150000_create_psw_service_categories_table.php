<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePswServiceCategoriesTable extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('psw_service_categories')) {
            Schema::create('psw_service_categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('psw_profile_id');
                $table->unsignedBigInteger('psw_id')->nullable();
                $table->unsignedBigInteger('service_category_id');
                $table->timestamps();

                $table->unique(['psw_profile_id', 'service_category_id'], 'psw_profile_service_unique');
                $table->unique(['psw_id', 'service_category_id'], 'psw_service_unique_by_psw');
                $table->index('psw_profile_id', 'psw_profile_idx');
                $table->index('psw_id', 'psw_id_idx');
                $table->index('service_category_id', 'service_category_idx');

                $table->foreign('psw_profile_id')
                    ->references('id')->on('psw_profiles')
                    ->onDelete('cascade');

                $table->foreign('service_category_id')
                    ->references('id')->on('service_categories')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('psw_service_categories');
    }
}
