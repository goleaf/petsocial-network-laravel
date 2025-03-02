<?php

namespace App\Models\Group;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{
    protected $table = 'group_user_roles';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Get the group user that owns the role assignment.
     */
    public function groupUser()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role that is assigned.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'group_role_id');
    }
}
