<?php

namespace App\Shared\Traits;

use App\Shared\Models\FileStorage;
use App\Shared\Enums\FileType;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Collection;

trait HasFiles
{
    /**
     * Get all files for this model
     */
    public function files(): MorphMany
    {
        return $this->morphMany(FileStorage::class, 'fileable');
    }

    /**
     * Get verified files only
     */
    public function verifiedFiles(): Collection
    {
        return $this->files()
            ->verified()
            ->notExpired()
            ->get();
    }

    /**
     * Get unverified files
     */
    public function unverifiedFiles(): Collection
    {
        return $this->files()
            ->where('is_verified', false)
            ->notExpired()
            ->get();
    }

    /**
     * Get public files
     */
    public function publicFiles(): Collection
    {
        return $this->files()
            ->public()
            ->notExpired()
            ->get();
    }

    /**
     * Get total file size for this model (in bytes)
     */
    public function getTotalFileSize(): int
    {
        return $this->files()
            ->notExpired()
            ->sum('file_size');
    }

    /**
     * Get human readable total file size
     */
    public function getTotalFileSizeHuman(): string
    {
        $totalSize = $this->getTotalFileSize();
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $totalSize >= 1024 && $i < count($units) - 1; $i++) {
            $totalSize /= 1024;
        }
        
        return round($totalSize, 2) . ' ' . $units[$i];
    }

    /**
     * Delete all files for this model
     */
    public function deleteAllFiles(): bool
    {
        return $this->files()->delete();
    }
}