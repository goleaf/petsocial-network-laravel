<?php

namespace Tests\Livewire {

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Livewire component that reuses the base controller to validate its internal state, ensuring the helper stays composable.
 */
class ControllerValidationComponent extends Component
{
    public string $name = '';

    public bool $validated = false;

    public function submit(): void
    {
        // Resolve the application controller from the container so we reuse the framework-managed traits.
        $controller = app(Controller::class);

        // Build a synthetic request mirroring the component state so the controller validation helper can process it.
        $request = Request::create('/livewire/controller-validation', 'POST', [
            'name' => $this->name,
        ]);

        try {
            // Attempt to validate the payload; success indicates the helper can be reused outside typical HTTP flows.
            $controller->validate($request, [
                'name' => ['required', 'string', 'min:3'],
            ]);

            // Reset the previous validation errors and flag the successful validation for the test assertion layer.
            $this->resetErrorBag();
            $this->validated = true;
        } catch (ValidationException $exception) {
            // Record the first validation message so Livewire accurately exposes the failure to the calling test.
            $this->addError('name', $exception->validator->errors()->first('name'));
            $this->validated = false;
        }
    }

    public function render(): string
    {
        // Provide a simple inline template because the test only inspects component state, not rendered output.
        return <<<'HTML'
            <div>
                <span>controller validation bridge</span>
            </div>
        HTML;
    }
}

}

namespace {

use Livewire\Livewire;
use Tests\Livewire\ControllerValidationComponent;

it('allows livewire components to delegate validation logic to the shared controller helper', function (): void {
    // Drive the Livewire component through invalid and valid submissions to verify error handling and success states.
    Livewire::test(ControllerValidationComponent::class)
        ->set('name', '')
        ->call('submit')
        ->assertHasErrors(['name'])
        ->set('name', 'Buddy')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('validated', true);
});

}
