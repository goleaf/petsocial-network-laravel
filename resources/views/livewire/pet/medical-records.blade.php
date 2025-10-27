@php
    // Import helper for string truncation inside the Blade template.
    use Illuminate\Support\Str;
@endphp

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Flash messages for record and visit operations -->
    @if (session()->has('medical_record_saved'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            {{ session('medical_record_saved') }}
        </div>
    @endif
    @if (session()->has('medical_record_error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            {{ session('medical_record_error') }}
        </div>
    @endif
    @if (session()->has('medical_visit_saved'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            {{ session('medical_visit_saved') }}
        </div>
    @endif
    @if (session()->has('medical_visit_deleted'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            {{ session('medical_visit_deleted') }}
        </div>
    @endif

    <!-- Medical record summary form -->
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">{{ __('pets.medical_records_title', ['name' => $pet->name]) }}</h1>
        <p class="text-sm text-gray-600 mb-6">{{ __('pets.medical_records_description') }}</p>

        <form wire:submit.prevent="saveRecord" class="space-y-6">
            <!-- Veterinary contact information section -->
            <div>
                <h2 class="text-lg font-semibold mb-3">{{ __('pets.medical_records_veterinary_header') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('pets.primary_veterinarian') }}</label>
                        <input type="text" wire:model.defer="primary_veterinarian" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('primary_veterinarian') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('pets.clinic_name') }}</label>
                        <input type="text" wire:model.defer="clinic_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('clinic_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('pets.clinic_contact') }}</label>
                        <input type="text" wire:model.defer="clinic_contact" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('clinic_contact') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('pets.last_checkup_at') }}</label>
                        <input type="date" wire:model.defer="last_checkup_at" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('last_checkup_at') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Insurance and identification section -->
            <div>
                <h2 class="text-lg font-semibold mb-3">{{ __('pets.medical_records_insurance_header') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('pets.insurance_provider') }}</label>
                        <input type="text" wire:model.defer="insurance_provider" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('insurance_provider') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('pets.insurance_policy_number') }}</label>
                        <input type="text" wire:model.defer="insurance_policy_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('insurance_policy_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('pets.microchip_id') }}</label>
                        <input type="text" wire:model.defer="microchip_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('microchip_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Health overview text areas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.known_conditions') }}</label>
                    <textarea wire:model.defer="known_conditions" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    @error('known_conditions') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.medications') }}</label>
                    <textarea wire:model.defer="medications" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    @error('medications') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.allergies') }}</label>
                    <textarea wire:model.defer="allergies" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    @error('allergies') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.vaccination_status') }}</label>
                    <textarea wire:model.defer="vaccination_status" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    @error('vaccination_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Additional safety notes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.dietary_notes') }}</label>
                    <textarea wire:model.defer="dietary_notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    @error('dietary_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.emergency_plan') }}</label>
                    <textarea wire:model.defer="emergency_plan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    @error('emergency_plan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    {{ __('pets.save_medical_record') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Visit history management -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">{{ __('pets.medical_visits_header') }}</h2>
        <p class="text-sm text-gray-600 mb-4">{{ __('pets.medical_visits_description') }}</p>

        <form wire:submit.prevent="saveVisit" class="space-y-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.visit_date') }}</label>
                    <input type="date" wire:model.defer="visit_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('visit_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.visit_veterinarian') }}</label>
                    <input type="text" wire:model.defer="visit_veterinarian" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('visit_veterinarian') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.visit_reason') }}</label>
                    <input type="text" wire:model.defer="visit_reason" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('visit_reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.visit_diagnosis') }}</label>
                    <input type="text" wire:model.defer="visit_diagnosis" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('visit_diagnosis') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('pets.visit_follow_up_date') }}</label>
                    <input type="date" wire:model.defer="visit_follow_up_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('visit_follow_up_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('pets.visit_treatment') }}</label>
                <textarea wire:model.defer="visit_treatment" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                @error('visit_treatment') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('pets.visit_medications_prescribed') }}</label>
                <textarea wire:model.defer="visit_medications_prescribed" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                @error('visit_medications_prescribed') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('pets.visit_notes') }}</label>
                <textarea wire:model.defer="visit_notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                @error('visit_notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    {{ $editingVisit ? __('pets.update_visit') : __('pets.add_visit') }}
                </button>
                @if ($editingVisit)
                    <button type="button" wire:click="resetVisitForm" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        {{ __('pets.cancel_visit_edit') }}
                    </button>
                @endif
            </div>
        </form>

        <!-- Visits table displaying medical history -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('pets.visit_date') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('pets.visit_veterinarian') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('pets.visit_reason') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('pets.visit_diagnosis') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('pets.visit_treatment') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('pets.visit_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($visits as $visit)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ optional($visit->visit_date)->format('Y-m-d') ?? __('pets.not_available') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $visit->veterinarian ?? __('pets.not_available') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $visit->reason ?? __('pets.not_available') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $visit->diagnosis ?? __('pets.not_available') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ Str::limit($visit->treatment ?? __('pets.not_available'), 60) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700 space-x-2">
                                <button wire:click="editVisit({{ $visit->id }})" class="text-indigo-600 hover:text-indigo-900">{{ __('pets.edit_visit') }}</button>
                                <button wire:click="deleteVisit({{ $visit->id }})" class="text-red-600 hover:text-red-900" onclick="return confirm('{{ __('pets.confirm_delete_visit') }}')">{{ __('pets.delete_visit') }}</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">{{ __('pets.no_medical_visits') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
