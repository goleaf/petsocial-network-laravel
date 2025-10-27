<?php

namespace Tests\Support;

use App\Models\User;

/**
 * Lightweight in-memory relation to emulate the missing post drafts table during tests.
 */
class FakePostDraftRelation
{
    /**
     * Hold the temporary draft payloads keyed by the owning user ID.
     *
     * @var array<int, array<string, mixed>>
     */
    protected static array $drafts = [];

    /**
     * Register the macro on the User model so the component can resolve drafts.
     */
    public static function register(): void
    {
        // Register the dynamic relation using Laravel's relation resolver to avoid polluting the model API.
        User::resolveRelationUsing('postDrafts', function (User $user) {
            return new FakePostDraftRelation($user);
        });
    }

    /**
     * Reset the stored drafts so each test starts with a clean slate.
     */
    public static function reset(): void
    {
        self::$drafts = [];
    }

    /**
     * Determine whether a draft is currently stored for the given user.
     */
    public static function draftFor(int $userId): ?array
    {
        return self::$drafts[$userId] ?? null;
    }

    /**
     * Spin up the fake relation bound to the authenticated user.
     */
    public function __construct(protected User $user)
    {
        // Intentionally empty: the fake relation only needs the owning user reference.
    }

    /**
     * Mimic Eloquent's updateOrCreate behaviour by storing the latest draft payload.
     */
    public function updateOrCreate(array $attributes, array $values): object
    {
        $id = $attributes['id'] ?? $values['id'] ?? uniqid();

        $draft = array_merge($values, [
            'id' => $id,
            'user_id' => $this->user->id,
        ]);

        self::$drafts[$this->user->id] = $draft;

        return (object) $draft;
    }

    /**
     * Provide a fluent API similar to the HasMany relation when chaining latest().
     */
    public function latest(): self
    {
        return $this;
    }

    /**
     * Return the stored draft if one exists for the active user.
     */
    public function first(): ?object
    {
        $draft = self::$drafts[$this->user->id] ?? null;

        return $draft ? (object) $draft : null;
    }

    /**
     * Support the where(...) -> delete() chaining the component expects.
     */
    public function where(string $column, mixed $value): self
    {
        return $this;
    }

    /**
     * Remove the in-memory draft entry for the active user.
     */
    public function delete(): bool
    {
        unset(self::$drafts[$this->user->id]);

        return true;
    }
}
