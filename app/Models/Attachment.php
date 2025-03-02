<?php

namespace App\Models\Merged;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'user_id',
        'original_filename',
        'disk_filename',
        'disk',
        'mime_type',
        'size',
        'type',
        'description',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the parent attachable model (post, message, etc.)
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded the attachment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include attachments of a certain type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include images
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Scope a query to only include videos
     */
    public function scopeVideos($query)
    {
        return $query->where('type', 'video');
    }

    /**
     * Scope a query to only include documents
     */
    public function scopeDocuments($query)
    {
        return $query->where('type', 'document');
    }

    /**
     * Get the URL for the attachment
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->disk_filename);
    }

    /**
     * Get the formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $i = 0;
        
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the attachment is an image
     */
    public function isImage(): bool
    {
        return $this->type === 'image' || strpos($this->mime_type, 'image/') === 0;
    }

    /**
     * Check if the attachment is a video
     */
    public function isVideo(): bool
    {
        return $this->type === 'video' || strpos($this->mime_type, 'video/') === 0;
    }

    /**
     * Check if the attachment is a document
     */
    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    /**
     * Delete the attachment from storage when the model is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($attachment) {
            Storage::disk($attachment->disk)->delete($attachment->disk_filename);
        });
    }
}
