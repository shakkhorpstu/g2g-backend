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
            if (! Schema::hasColumn('psw_profiles', 'has_own_vehicle')) {
                $table->boolean('has_own_vehicle')->default(false)->after('driving_allowance_per_km');
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
            if (Schema::hasColumn('psw_profiles', 'has_own_vehicle')) {
                $table->dropColumn('has_own_vehicle');
            }
        });
    }
};
