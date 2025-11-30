<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfileDocumentsTable extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('profile_documents')) {
            Schema::create('profile_documents', function (Blueprint $table) {
                $table->bigIncrements('id');

                // polymorphic owner (UserProfile or PswProfile)
                $table->string('documentable_type');
                $table->unsignedBigInteger('documentable_id');

                // reference to document type
                $table->unsignedBigInteger('document_type_id');
                $table->enum('document_side_key', ['front', 'back'])->nullable(); // front or back side if applicable

                // status (pending, uploaded, verified, rejected)
                $table->string('status')->default('pending');

                // who uploaded / verified (polymorphic)
                $table->string('uploaded_by_type')->nullable();
                $table->unsignedBigInteger('uploaded_by_id')->nullable();

                $table->unsignedBigInteger('verified_by_id')->nullable();
                $table->timestamp('verified_at')->nullable();

                // expiry and metadata
                $table->json('metadata')->nullable();
                $table->text('admin_notes')->nullable();

                $table->timestamps();

                $table->index(['documentable_type', 'documentable_id'], 'profile_documents_docable_idx');
                $table->index('document_type_id', 'profile_documents_type_idx');
                $table->index('status', 'profile_documents_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_documents');
    }
}
