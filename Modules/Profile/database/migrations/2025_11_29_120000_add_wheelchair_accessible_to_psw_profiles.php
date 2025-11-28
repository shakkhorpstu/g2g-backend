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
            if (! Schema::hasColumn('psw_profiles', 'has_wheelchair_accessible_vehicle')) {
                $table->boolean('has_wheelchair_accessible_vehicle')->default(false)->after('has_own_vehicle');
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
            if (Schema::hasColumn('psw_profiles', 'has_wheelchair_accessible_vehicle')) {
                $table->dropColumn('has_wheelchair_accessible_vehicle');
            }
        });
    }
};
