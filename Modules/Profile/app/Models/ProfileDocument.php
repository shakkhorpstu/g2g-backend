<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Shared\Models\FileStorage;

class ProfileDocument extends Model
{
    protected $table = 'profile_documents';

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'document_type_id',
        'document_side_key',
        'status',
        'uploaded_by_type',
        'uploaded_by_id',
        'verified_by_id',
        'verified_at',
        'metadata',
        'admin_notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'verified_at' => 'datetime',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    /**
     * Files attached to this logical document (front/back) stored in shared FileStorage
     */
    public function files(): MorphMany
    {
        return $this->morphMany(FileStorage::class, 'fileable');
    }

    public function frontFile()
    {
        return $this->files()->where('file_category', 'front')->latest()->first();
    }

    public function backFile()
    {
        return $this->files()->where('file_category', 'back')->latest()->first();
    }
}
