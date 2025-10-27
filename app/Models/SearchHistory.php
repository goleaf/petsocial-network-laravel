<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SearchHistory
 *
 * Represents an executed search query so the interface can provide personalised history lists
 * and analytics around frequently explored topics.
 */
class SearchHistory extends Model
{
    /**
     * The attributes that are mass assignable for this model.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'query', 'search_type', 'filters', 'results_count'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters' => 'array',
    ];

    /**
     * Provide the owning user relationship for the history entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
