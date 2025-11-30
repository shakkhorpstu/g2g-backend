<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $table = 'document_types';

    protected $fillable = [
        'key',
        'title',
        'icon',
        'description',
        'both_sided',
        'both_sided_required',
        'front_side_title',
        'back_side_title',
        'allowed_mime',
        'max_size_kb',
        'active',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'both_sided' => 'boolean',
        'both_sided_required' => 'boolean',
        'allowed_mime' => 'array',
        'required_for_psw' => 'boolean',
        'required_for_user' => 'boolean',
        'active' => 'boolean',
    ];
}
