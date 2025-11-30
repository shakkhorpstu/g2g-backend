<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentTypesTable extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('document_types')) {
            Schema::create('document_types', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('key')->unique();
                $table->string('title');
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->boolean('both_sided')->default(false);
                $table->boolean('both_sided_required')->default(false);
                $table->string('front_side_title')->nullable();
                $table->string('back_side_title')->nullable();
                $table->json('allowed_mime')->nullable();
                $table->integer('max_size_kb')->nullable();
                $table->boolean('active')->default(true);
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
}
