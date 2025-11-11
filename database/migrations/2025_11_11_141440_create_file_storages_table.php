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
        Schema::create('file_storages', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship
            $table->string('fileable_type');
            $table->unsignedBigInteger('fileable_id');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('file_path');
            $table->string('file_url')->nullable();
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            
            // Status and permissions
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_public')->default(false);
            
            // Upload tracking
            $table->string('uploaded_by_type')->nullable();
            $table->unsignedBigInteger('uploaded_by_id')->nullable();
            
            // Additional data
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['fileable_type', 'fileable_id'], 'file_storages_fileable_index');
            $table->index(['file_type', 'fileable_type'], 'file_storages_type_index');
            $table->index(['is_verified', 'file_type'], 'file_storages_verified_index');
            $table->index(['uploaded_by_type', 'uploaded_by_id'], 'file_storages_uploader_index');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_storages');
    }
};
