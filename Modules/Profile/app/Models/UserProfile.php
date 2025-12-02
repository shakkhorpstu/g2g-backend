<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Profile\Models\ProfilePreference;

class UserProfile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\User::class);
    }

    /**
     * Preferences associated with this profile.
     */
    public function preferences()
    {
        return $this->morphMany(ProfilePreference::class, 'owner');
    }

    /**
     * Get the profile picture file for this profile.
     */
    public function profilePicture()
    {
        return $this->morphOne(\App\Shared\Models\FileStorage::class, 'fileable')
            ->where('file_type', 'profile_picture')
            ->orderBy('created_at', 'desc');
    }
}
