<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ActivityLog
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string $description
 * @property string $severity
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $metadata
 */
class ActivityLog extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'severity',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Create a new activity log entry with standardized metadata handling.
     */
    public static function record(
        User $user,
        string $action,
        string $description,
        array $metadata = [],
        string $severity = 'info',
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        $resolvedIp = $ipAddress ?? request()?->ip();
        $resolvedUserAgent = $userAgent ?? request()?->userAgent();

        $normalizedMetadata = array_filter($metadata, static fn ($value) => ! is_null($value));

        return $user->activityLogs()->create([
            'action' => $action,
            'description' => $description,
            'severity' => $severity,
            'ip_address' => $resolvedIp,
            'user_agent' => $resolvedUserAgent,
            'metadata' => empty($normalizedMetadata) ? null : $normalizedMetadata,
        ]);
    }

    /**
     * The user associated with this activity log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
