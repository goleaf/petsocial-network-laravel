<?php

namespace App\Models\Group;

use App\Models\AbstractModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Resource extends AbstractModel
{
    /**
     * Attributes that can be mass assigned when storing group resources.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'user_id',
        'title',
        'description',
        'type',
        'url',
        'file_path',
        'file_name',
        'file_size',
        'file_mime',
    ];

    /**
     * Cast attributes into richer PHP types for convenience.
     */
    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Cached relationships should refresh whenever the record mutates.
     */
    protected static function booted(): void
    {
        parent::booted();

        static::saved(function (self $resource): void {
            // Flush cached collections so group detail views reflect the latest entries.
            $resource->clearCache();
            $resource->group->clearCache();
        });

        static::deleted(function (self $resource): void {
            // Clear caches and remove the stored document if one exists on disk.
            $resource->clearCache();
            $resource->group->clearCache();

            if ($resource->file_path !== null) {
                Storage::disk('public')->delete($resource->file_path);
            }
        });
    }

    /**
     * Relationship back to the parent group that owns the resource.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Relationship to the member who shared the resource.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Determine if the resource entry references an external link.
     */
    public function isLink(): bool
    {
        return $this->type === 'link';
    }

    /**
     * Determine if the resource entry contains an uploaded document.
     */
    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    /**
     * Resolve a publicly accessible URL for downloaded documents.
     */
    public function getDocumentUrlAttribute(): ?string
    {
        if ($this->file_path === null) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Retrieve a cached collection of resources for the provided group.
     */
    public static function forGroup(int $groupId)
    {
        $cacheKey = sprintf('group_%s_resources', $groupId);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($groupId) {
            // Order newest first so the UI surfaces recent contributions.
            return static::query()
                ->with('author')
                ->where('group_id', $groupId)
                ->latest()
                ->get();
        });
    }

    /**
     * Clear cache entries dedicated to the owning group when this model updates.
     */
    public function clearCache(): void
    {
        Cache::forget(sprintf('group_%s_resources', $this->group_id));
        parent::clearCache();
    }
}
