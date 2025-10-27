<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SavedSearch
 *
 * Stores reusable search definitions so members can revisit advanced discovery filters and track
 * how often those saved searches are executed.
 */
class SavedSearch extends Model
{
    /**
     * The attributes that can be mass assigned on the model.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'name', 'query', 'search_type', 'filters', 'run_count'];

    /**
     * Cast attributes to appropriate data types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters' => 'array',
    ];

    /**
     * Provide the relationship back to the owning user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
