<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Do not run this migration inside a transaction because raw index operations
     * may cause Postgres to mark the transaction as failed and block subsequent
     * statements.
     */
    public $withinTransaction = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the unique constraint so multiple languages can be stored per owner.
        if (! Schema::hasTable('profile_languages')) {
            return;
        }

        // Try Postgres-style DROP INDEX IF EXISTS first. If that fails, fall back
        // to the Schema builder dropUnique. Any errors will be ignored to make
        // this migration idempotent.
        try {
            DB::statement('DROP INDEX IF EXISTS profile_languages_unique');
        } catch (\Throwable $e) {
            try {
                Schema::table('profile_languages', function (Blueprint $table) {
                    // Attempt to drop the named unique index. If it does not exist,
                    // this call may throw; catch it in the outer try/catch.
                    $table->dropUnique('profile_languages_unique');
                });
            } catch (\Throwable $e) {
                // swallow: index may not exist or name may differ across DBs
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_languages', function (Blueprint $table) {
            $table->unique(['languageable_type', 'languageable_id'], 'profile_languages_unique');
        });
    }
};
