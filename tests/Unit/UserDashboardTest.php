<?php

namespace Tests\Unit;

use App\Http\Livewire\UserDashboard;
use Livewire\WithPagination;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Unit coverage for structural expectations on the UserDashboard component.
 */
class UserDashboardTest extends TestCase
{
    /**
     * Ensure the component keeps the pagination trait required for chunked feeds.
     */
    public function test_it_uses_pagination_trait(): void
    {
        // Capture every trait applied to the component, including inherited ones.
        $traits = class_uses_recursive(UserDashboard::class);

        // Validate the WithPagination contract remains present for page navigation.
        $this->assertContains(WithPagination::class, $traits);
    }

    /**
     * Confirm the event listener map continues pointing to the loadPosts handler.
     */
    public function test_it_exposes_expected_event_listeners(): void
    {
        // Reflect into the component so the protected $listeners array can be inspected safely.
        $reflection = new ReflectionClass(UserDashboard::class);
        $listenersProperty = $reflection->getProperty('listeners');
        $listenersProperty->setAccessible(true);

        // Instantiate the component to read the resolved listener mapping at runtime.
        $component = new UserDashboard();
        $listeners = $listenersProperty->getValue($component);

        // Ensure the post lifecycle events continue to hydrate the feed through loadPosts.
        $this->assertSame([
            'postCreated' => 'loadPosts',
            'postUpdated' => 'loadPosts',
            'postDeleted' => 'loadPosts',
        ], $listeners);
    }

    /**
     * Guarantee the render method continues to point at the correct Blade view and layout wrapper.
     */
    public function test_render_method_targets_dashboard_blade(): void
    {
        // Capture the source lines for the render method so we can validate the referenced view path.
        $renderMethod = new ReflectionMethod(UserDashboard::class, 'render');
        $methodLines = file($renderMethod->getFileName());
        $methodBody = implode('', array_slice(
            $methodLines,
            $renderMethod->getStartLine() - 1,
            $renderMethod->getEndLine() - $renderMethod->getStartLine() + 1
        ));

        // Ensure the component keeps returning the livewire.user-dashboard Blade view wrapped in the app layout.
        $this->assertStringContainsString("view('livewire.user-dashboard')", $methodBody);
        $this->assertStringContainsString("layout('layouts.app')", $methodBody);
    }
}
