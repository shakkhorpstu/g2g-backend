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
        if (Schema::hasTable('user_profiles')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('user_profiles', '2fa_enabled')) {
                    $table->boolean('2fa_enabled')->default(false)->after('updated_at');
                }

                if (!Schema::hasColumn('user_profiles', '2fa_identifier_key')) {
                    $table->enum('2fa_identifier_key', ['phone', 'email'])->nullable()->after('2fa_enabled');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_profiles')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('user_profiles', '2fa_identifier_key')) {
                    $table->dropColumn('2fa_identifier_key');
                }

                if (Schema::hasColumn('user_profiles', '2fa_enabled')) {
                    $table->dropColumn('2fa_enabled');
                }
            });
        }
    }
};
