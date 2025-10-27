<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserSettings extends Component
{
    public $name;

    public $email;

    public $password;

    public $password_confirmation;

    public $current_password;

    public $profile_visibility;

    public $posts_visibility;

    /**
     * Store the visibility selection for each privacy-controlled section.
     */
    public $privacySettings = [];

    /**
     * Provide contextual feedback when an audience preset is applied.
     */
    public ?string $privacyPresetNotice = null;

    public $showDeactivateModal = false;

    public $showDeleteModal = false;

    public $confirmPassword;

    /**
     * Structured notification preferences bound to the settings form.
     */
    public array $notificationPreferences = [];

    /**
     * Describes all available categories for filtering and labels.
     */
    public array $notificationCategories = [];

    /**
     * Exposes frequency options for select inputs.
     */
    public array $notificationFrequencies = [];

    /**
     * Exposes available priorities to keep user choices bounded.
     */
    public array $notificationPriorities = [];

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->profile_visibility = $user->profile_visibility;
        $this->posts_visibility = $user->posts_visibility;
        $this->privacySettings = array_merge(User::PRIVACY_DEFAULTS, $user->privacy_settings ?? []);

        $service = app(NotificationService::class);
        $this->notificationPreferences = $service->preferencesFor($user);
        $this->notificationCategories = Config::get('notifications.categories', []);
        $this->notificationFrequencies = Config::get('notifications.frequencies', []);
        $this->notificationPriorities = Config::get('notifications.priorities', []);
    }

    public function update()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.auth()->id(),
            'profile_visibility' => 'required|in:public,friends,private',
            'posts_visibility' => 'required|in:public,friends',
        ];

        foreach (array_keys(User::PRIVACY_DEFAULTS) as $section) {
            $rules['privacySettings.'.$section] = 'required|in:'.implode(',', User::PRIVACY_VISIBILITY_OPTIONS);
        }

        $this->validate($rules);

        $sanitizedPrivacy = array_intersect_key($this->privacySettings, User::PRIVACY_DEFAULTS);
        $this->privacySettings = array_merge(User::PRIVACY_DEFAULTS, $sanitizedPrivacy);

        $service = app(NotificationService::class);
        $cleanPreferences = $service->cleanPreferences(auth()->user(), $this->notificationPreferences);
        $this->notificationPreferences = $cleanPreferences;

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'profile_visibility' => $this->profile_visibility,
            'posts_visibility' => $this->posts_visibility,
            'privacy_settings' => $this->privacySettings,
            'notification_preferences' => $cleanPreferences,
        ];

        auth()->user()->update($data);
        session()->flash('message', 'Settings updated!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('message', 'Password updated successfully!');
    }

    public function confirmDeactivate()
    {
        $this->validate([
            'confirmPassword' => 'required|current_password',
        ]);

        auth()->user()->update([
            'deactivated_at' => now(),
        ]);

        auth()->logout();

        return redirect()->route('login')->with('status', 'Your account has been deactivated.');
    }

    public function confirmDelete()
    {
        $this->validate([
            'confirmPassword' => 'required|current_password',
        ]);

        // This will redirect to the AccountController's delete method
        return redirect()->route('account.delete');
    }

    public function toggleNotification($type)
    {
        if (isset($this->notificationPreferences['categories'][$type])) {
            $this->notificationPreferences['categories'][$type]['enabled'] = ! $this->notificationPreferences['categories'][$type]['enabled'];
        }
    }

    public function render()
    {
        return view('livewire.user-settings', [
            'twoFactorEnabled' => auth()->user()->two_factor_enabled,
            'privacySections' => $this->privacySections(),
            'privacyPresets' => $this->privacyPresets(),
        ])->layout('layouts.app');
    }

    /**
     * Provide translated labels for each privacy controlled section.
     */
    protected function privacySections(): array
    {
        return [
            'basic_info' => __('common.privacy_section_basic_info'),
            'stats' => __('common.privacy_section_stats'),
            'friends' => __('common.privacy_section_friends'),
            'mutual_friends' => __('common.privacy_section_mutual_friends'),
            'pets' => __('common.privacy_section_pets'),
            'activity' => __('common.privacy_section_activity'),
        ];
    }

    /**
     * Describe the quick audience presets available for privacy settings.
     */
    protected function privacyPresets(): array
    {
        return [
            'public' => __('common.privacy_preset_public'),
            'friends' => __('common.privacy_preset_friends'),
            'private' => __('common.privacy_preset_private'),
        ];
    }

    /**
     * Apply an audience preset across every privacy controlled section.
     */
    public function applyPrivacyPreset(string $preset): void
    {
        if (! in_array($preset, User::PRIVACY_VISIBILITY_OPTIONS, true)) {
            return;
        }

        foreach (array_keys(User::PRIVACY_DEFAULTS) as $section) {
            $this->privacySettings[$section] = $preset;
        }

        $presetLabel = $this->privacyPresets()[$preset] ?? $preset;

        $this->privacyPresetNotice = __('common.privacy_preset_applied', [
            'preset' => $presetLabel,
        ]);
    }
}
