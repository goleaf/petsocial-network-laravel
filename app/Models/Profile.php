<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Profile encapsulates metadata and media associated with a user account.
 */
class Profile extends Model
{
    /**
     * Attributes allowed for mass assignment to support rich profile customization.
     *
     * @var array<int, string>
     */
    protected $fillable = ['bio', 'avatar', 'cover_photo', 'user_id', 'location'];

    /**
     * Provide access to the owning user relationship for this profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
