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
        Schema::table('profile_documents', function (Blueprint $table) {
            $table->dropColumn('document_side_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_documents', function (Blueprint $table) {
            $table->enum('document_side_key', ['front', 'back'])->nullable()->after('document_type_id');
        });
    }
};
