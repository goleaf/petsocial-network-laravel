<?php

namespace App\Models;

use App\Models\Traits\HasPolymorphicRelations;
use App\Traits\ActivityTrait;
use App\Traits\EntityTypeTrait;
use App\Traits\HasFriendships;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Pet extends Model
{
    use ActivityTrait;
    use EntityTypeTrait;
    use HasFactory;
    use HasFriendships;
    use HasPolymorphicRelations;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'breed',
        'birthdate',
        'avatar',
        'location',
        'bio',
        'favorite_food',
        'favorite_toy',
        'is_public',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get posts associated with this pet
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Initialize the entity type and ID for the Pet model
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($pet) {
            $pet->initializeEntity('pet', $pet->id);
        });

        static::retrieved(function ($pet) {
            $pet->initializeEntity('pet', $pet->id);
        });
    }

    /**
     * Get the pet's avatar URL with a default if none exists
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar
                ? asset('storage/'.$this->avatar)
                : asset('images/default-pet-avatar.png')
        );
    }

    /**
     * Get the pet's age based on birthdate
     */
    protected function age(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->birthdate) {
                    return null;
                }

                $interval = Carbon::now()->diff($this->birthdate);

                if ($interval->y > 0) {
                    return $interval->y.' '.($interval->y == 1 ? 'year' : 'years');
                } elseif ($interval->m > 0) {
                    return $interval->m.' '.($interval->m == 1 ? 'month' : 'months');
                } else {
                    return $interval->d.' '.($interval->d == 1 ? 'day' : 'days');
                }
            }
        );
    }

    /**
     * Get the activities for this pet
     */
    public function activities(): HasMany
    {
        return $this->hasMany(PetActivity::class)->orderBy('happened_at', 'desc');
    }

    /**
     * Get the recent activities for this pet with caching
     */
    public function recentActivities($limit = 5)
    {
        $cacheKey = "pet_{$this->id}_recent_activities_{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($limit) {
            return $this->activities()->limit($limit)->get();
        });
    }

    /**
     * Get the notifications for this pet
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(PetNotification::class, 'pet_id')->orderBy('created_at', 'desc');
    }

    /**
     * Define the relationship to the private medical record entry.
     * We use hasOne because each pet maintains a single aggregated record.
     */
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(PetMedicalRecord::class);
    }

    /**
     * Get unread notifications count with caching
     */
    public function unreadNotificationsCount(): int
    {
        $cacheKey = "pet_{$this->id}_unread_notifications_count";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->notifications()->where('read', false)->count();
        });
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(): void
    {
        $this->notifications()->where('read', false)->update(['read' => true]);
        Cache::forget("pet_{$this->id}_unread_notifications_count");
    }

    /**
     * Export friends to CSV
     */
    public function exportFriendsToCSV(): string
    {
        $friends = $this->prepareFriendExportRows();

        $handle = fopen('php://temp', 'w+');

        fputcsv($handle, ['Name', 'Type', 'Breed', 'Category', 'Since', 'Owner', 'Owner Email', 'Owner Phone']);

        foreach ($friends as $friend) {
            fputcsv($handle, [
                $friend['name'],
                $friend['type'],
                $friend['breed'],
                $friend['category'],
                $friend['since'],
                $friend['owner_name'],
                $friend['owner_email'],
                $friend['owner_phone'],
            ]);
        }

        rewind($handle);

        $contents = stream_get_contents($handle);

        fclose($handle);

        return $this->storeFriendExport('csv', $contents);
    }

    /**
     * Export the pet's accepted friendships as a JSON document including owner contact details.
     */
    public function exportFriendsToJson(): string
    {
        $friends = $this->prepareFriendExportRows()->map(function (array $friend): array {
            return [
                'name' => $friend['name'],
                'type' => $friend['type'],
                'breed' => $friend['breed'],
                'category' => $friend['category'],
                'since' => $friend['since'],
                'owner' => [
                    'name' => $friend['owner_name'],
                    'email' => $friend['owner_email'],
                    'phone' => $friend['owner_phone'],
                ],
            ];
        });

        $contents = $friends->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $this->storeFriendExport('json', $contents);
    }

    /**
     * Export the pet's accepted friendships in vCard format for contact import tooling.
     */
    public function exportFriendsToVcf(): string
    {
        $cards = $this->prepareFriendExportRows()->map(function (array $friend): string {
            $ownerName = $friend['owner_name'] ?? $friend['name'].' Owner';
            $noteParts = array_filter([
                $friend['type'] ? 'Pet Type: '.$friend['type'] : null,
                $friend['breed'] ? 'Breed: '.$friend['breed'] : null,
                $friend['category'] ? 'Category: '.$friend['category'] : null,
                $friend['since'] ? 'Friends Since: '.$friend['since'] : null,
            ]);

            $note = empty($noteParts) ? null : implode('; ', $noteParts);

            $lines = [
                'BEGIN:VCARD',
                'VERSION:3.0',
                'FN:'.$ownerName,
                'N:'.$ownerName.';;;;',
                'NICKNAME:'.$friend['name'],
            ];

            if (! empty($friend['owner_email'])) {
                $lines[] = 'EMAIL;TYPE=INTERNET:'.$friend['owner_email'];
            }

            if (! empty($friend['owner_phone'])) {
                $lines[] = 'TEL;TYPE=CELL:'.$friend['owner_phone'];
            }

            if ($note) {
                $lines[] = 'NOTE:'.$note;
            }

            $lines[] = 'END:VCARD';

            return implode("\r\n", $lines);
        })->implode("\r\n");

        return $this->storeFriendExport('vcf', $cards);
    }

    /**
     * Normalize accepted pet friendships into export-friendly rows with owner metadata.
     */
    protected function prepareFriendExportRows(): Collection
    {
        return PetFriendship::query()
            ->with(['pet.user', 'friendPet.user'])
            ->where(function ($query) {
                $query->where('pet_id', $this->id)
                    ->orWhere('friend_pet_id', $this->id);
            })
            ->accepted()
            ->get()
            ->map(function (PetFriendship $friendship) {
                $friend = $friendship->pet_id === $this->id
                    ? $friendship->friendPet
                    : $friendship->pet;

                if (! $friend) {
                    return null;
                }

                $owner = $friend->relationLoaded('user') ? $friend->user : $friend->user()->first();

                return [
                    'name' => $friend->name,
                    'type' => $friend->type,
                    'breed' => $friend->breed,
                    'category' => $friendship->category,
                    'since' => optional($friendship->accepted_at ?? $friendship->created_at)->toDateString(),
                    'owner_name' => optional($owner)->name,
                    'owner_email' => optional($owner)->email,
                    'owner_phone' => optional($owner)->phone,
                ];
            })
            ->filter()
            ->sortBy('name')
            ->values();
    }

    /**
     * Persist the generated export to the public storage disk and return its accessible URL.
     */
    protected function storeFriendExport(string $extension, string $contents): string
    {
        $slug = Str::slug($this->name, '_') ?: 'pet';
        $timestamp = now()->format('Y-m-d_His');
        $path = "exports/{$slug}_friends_{$timestamp}.{$extension}";

        Storage::disk('public')->makeDirectory('exports');
        Storage::disk('public')->put($path, $contents);

        return Storage::disk('public')->url($path);
    }
}
