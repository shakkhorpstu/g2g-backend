<?php

namespace App\Shared\Models;

use App\Shared\Enums\FileType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class FileStorage extends Model
{
    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'original_name',
        'stored_name',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'is_verified',
        'is_public',
        'uploaded_by_type',
        'uploaded_by_id',
        'metadata',
        'expires_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_public' => 'boolean',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * Get the parent fileable model (User, PSW, Admin, etc.)
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the uploader model
     */
    public function uploader(): MorphTo
    {
        return $this->morphTo('uploaded_by');
    }

    /**
     * Get the full Digital Ocean Spaces URL
     */
    public function getFullUrlAttribute(): string
    {
        if ($this->file_url) {
            return $this->file_url;
        }
        
        $baseUrl = config('filesystems.disks.do_spaces.url', '');
        return rtrim($baseUrl, '/') . '/' . ltrim($this->file_path, '/');
    }

    /**
     * Get secure download URL (for private files)
     */
    public function getSecureUrlAttribute(): string
    {
        return Storage::disk('do_spaces')->temporaryUrl(
            $this->file_path,
            now()->addMinutes(30)
        );
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if file is a document
     */
    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Get human readable file size
     */
    public function getHumanFileSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->file_size;
        
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Get file extension from stored name
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->stored_name, PATHINFO_EXTENSION);
    }

    /**
     * Check if file is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope for getting files by type
     */
    public function scopeByType($query, string $fileType)
    {
        return $query->where('file_type', $fileType);
    }

    /**
     * Scope for getting verified files only
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for getting public files only
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for getting non-expired files
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for files by owner
     */
    public function scopeForOwner($query, $owner)
    {
        return $query->where('fileable_type', get_class($owner))
                     ->where('fileable_id', $owner->id);
    }

    /**
     * Get file type enum instance
     */
    public function getFileTypeEnum(): ?FileType
    {
        return FileType::tryFrom($this->file_type);
    }

    /**
     * Check if file exists in storage
     */
    public function existsInStorage(): bool
    {
        return Storage::disk('do_spaces')->exists($this->file_path);
    }

    /**
     * Delete file from storage when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($fileStorage) {
            if ($fileStorage->existsInStorage()) {
                Storage::disk('do_spaces')->delete($fileStorage->file_path);
            }
        });
    }
}