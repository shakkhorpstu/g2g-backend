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
        if (! Schema::hasTable('psw_profiles')) {
            return;
        }

        if (! Schema::hasColumn('psw_profiles', 'min_booking_slot')) {
            Schema::table('psw_profiles', function (Blueprint $table) {
                $table->unsignedSmallInteger('min_booking_slot')->default(30)->after('has_wheelchair_accessible_vehicle')->nullable(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('psw_profiles')) {
            return;
        }

        if (Schema::hasColumn('psw_profiles', 'min_booking_slot')) {
            Schema::table('psw_profiles', function (Blueprint $table) {
                $table->dropColumn('min_booking_slot');
            });
        }
    }
};
