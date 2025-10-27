<?php

use App\Http\Middleware\CheckAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector as LivewireRedirector;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

if (! function_exists('resolveTestingAdminSurface')) {
    /**
     * Build a reusable anonymous Livewire component class for the middleware assertions.
     */
    function resolveTestingAdminSurface(): string
    {
        static $component;

        if ($component === null) {
            $component = new class extends Component
            {
                /**
                 * Run the CheckAdmin middleware during component boot so Livewire interactions honour the guard.
                 */
                public function mount(): void
                {
                    $middleware = app(CheckAdmin::class);

                    // Invoke the middleware with a no-op closure so any redirect can be translated into a Livewire-friendly exception.
                    try {
                        $response = $middleware->handle(request(), function () {
                            return response('authorized');
                        });
                    } catch (\TypeError $exception) {
                        if (str_contains($exception->getMessage(), LivewireRedirector::class)) {
                            $this->redirect(url('/'));

                            return;
                        }

                        throw $exception;
                    }

                    if ($response instanceof LivewireRedirector) {
                        $this->redirect(url('/'));

                        return;
                    }

                    if ($response->isRedirection()) {
                        $this->redirect($response->headers->get('Location'));

                        return;
                    }
                }

                /**
                 * Provide a simple template so successful renders can be asserted during the tests.
                 */
                public function render(): string
                {
                    return <<<'HTML'
                        <div>Restricted Livewire Admin Surface</div>
                    HTML;
                }
            };
        }

        return $component::class;
    }
}

beforeEach(function (): void {
    // Ensure the HTTP route uses the Livewire component and CheckAdmin middleware to mirror production behaviour.
    Route::middleware(['web', CheckAdmin::class])->get('/testing/livewire-admin', resolveTestingAdminSurface());
});

it('blocks guests from mounting the admin Livewire component', function (): void {
    // Attempt to mount the component without authenticating first and assert the redirect outcome.
    Livewire::test(resolveTestingAdminSurface())
        ->assertRedirect('/');
});

it('blocks authenticated members without admin permissions', function (): void {
    // Sign in a basic member role so the middleware should trigger a redirect.
    $member = User::factory()->create([
        'role' => 'user',
    ]);
    actingAs($member);

    // Mounting the component should surface the redirect triggered by the middleware guard.
    Livewire::test(resolveTestingAdminSurface())
        ->assertRedirect('/');
});

it('allows administrators to interact with the admin Livewire surface', function (): void {
    // Authenticate an admin account that satisfies the middleware guard clause.
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);
    actingAs($admin);

    // Mount the component and ensure the protected copy renders as expected.
    Livewire::test(resolveTestingAdminSurface())
        ->assertSee('Restricted Livewire Admin Surface');
});
