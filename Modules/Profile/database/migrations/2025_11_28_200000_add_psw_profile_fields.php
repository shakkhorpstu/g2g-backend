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
        if (! Schema::hasTable('psw_profiles')) {
            return;
        }

        Schema::table('psw_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('psw_profiles', 'available_status')) {
                $table->boolean('available_status')->default(false)->after('psw_id');
            }

            if (! Schema::hasColumn('psw_profiles', 'hourly_rate')) {
                $table->decimal('hourly_rate', 10, 2)->nullable()->after('available_status');
            }

            if (! Schema::hasColumn('psw_profiles', 'include_driving_allowance')) {
                $table->boolean('include_driving_allowance')->default(false)->after('hourly_rate');
            }

            if (! Schema::hasColumn('psw_profiles', 'driving_allowance_per_km')) {
                $table->decimal('driving_allowance_per_km', 10, 2)->nullable()->after('include_driving_allowance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('psw_profiles')) {
            return;
        }

        Schema::table('psw_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('psw_profiles', 'driving_allowance_per_km')) {
                $table->dropColumn('driving_allowance_per_km');
            }

            if (Schema::hasColumn('psw_profiles', 'include_driving_allowance')) {
                $table->dropColumn('include_driving_allowance');
            }

            if (Schema::hasColumn('psw_profiles', 'hourly_rate')) {
                $table->dropColumn('hourly_rate');
            }

            if (Schema::hasColumn('psw_profiles', 'available_status')) {
                $table->dropColumn('available_status');
            }
        });
    }
};
