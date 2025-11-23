<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stripe_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('stripe_transactions', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('raw_payload');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stripe_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('stripe_transactions', 'refunded_at')) {
                $table->dropColumn('refunded_at');
            }
        });
    }
};
