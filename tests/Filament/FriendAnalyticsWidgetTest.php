<?php

use App\Http\Livewire\Common\Friend\Analytics;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

// Provide a lightweight stand-in for Filament's widget base when the package
// is not installed in the test environment.
if (! class_exists(\Filament\Widgets\Widget::class)) {
    class_alias(\Livewire\Component::class, 'Filament\\Widgets\\Widget');
}

// Minimal widget wrapper that would embed the analytics component within a
// Filament dashboard card.
class FriendAnalyticsWidget extends \Filament\Widgets\Widget
{
    public Analytics $analyticsComponent;

    /**
     * Simulate the mounting workflow Filament would execute when rendering the
     * widget on a dashboard.
     */
    public function mountWidget(int $entityId): void
    {
        $this->analyticsComponent = app(Analytics::class);
        $this->analyticsComponent->mount('user', $entityId);
    }
}

it('embeds the analytics component inside a filament widget context', function () {
    // Reset cached relationship lookups to guarantee deterministic assertions.
    Cache::flush();

    $member = User::factory()->create();
    $friend = User::factory()->create();

    actingAs($member);

    // Establish at least one friendship so the widget surfaces non-zero data.
    Friendship::query()->create([
        'sender_id' => $member->id,
        'recipient_id' => $friend->id,
        'status' => Friendship::STATUS_ACCEPTED,
        'accepted_at' => now()->subDay(),
        'created_at' => now()->subDays(2),
        'updated_at' => now()->subDay(),
    ]);

    $widget = new FriendAnalyticsWidget();
    $widget->mountWidget($member->id);

    expect($widget->analyticsComponent)
        ->toBeInstanceOf(Analytics::class)
        ->and($widget->analyticsComponent->summary['total_friends'])->toBe(1);
});
