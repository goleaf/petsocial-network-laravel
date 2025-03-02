<?php

namespace App\Models\Traits;

use App\Models\PetActivity;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Reaction;

trait HasPolymorphicRelations
{
    /**
     * Get all activities where this model is the actor
     */
    public function activities()
    {
        return $this->morphMany(PetActivity::class, 'actor');
    }

    /**
     * Get all activities where this model is the target
     */
    public function targetedActivities()
    {
        return $this->morphMany(PetActivity::class, 'target');
    }

    /**
     * Get all attachments for this model
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all comments for this model
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all reactions for this model
     */
    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }
}
