<?php

namespace App\Http\Livewire;

use App\Models\User;
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
    public $privacySettings = [];
    public $showDeactivateModal = false;
    public $showDeleteModal = false;
    public $confirmPassword;
    
    // Notification preferences
    public $notificationPreferences = [
        'messages' => true,
        'friend_requests' => true,
        'post_comments' => true,
        'post_likes' => true,
        'event_reminders' => true,
        'group_activity' => true,
        'email_notifications' => true,
        'push_notifications' => true
    ];

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->profile_visibility = $user->profile_visibility;
        $this->posts_visibility = $user->posts_visibility;
        $this->privacySettings = array_merge(User::PRIVACY_DEFAULTS, $user->privacy_settings ?? []);
        
        // Load notification preferences if they exist
        if ($user->notification_preferences) {
            $this->notificationPreferences = array_merge(
                $this->notificationPreferences,
                $user->notification_preferences
            );
        }
    }

    public function update()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'profile_visibility' => 'required|in:public,friends,private',
            'posts_visibility' => 'required|in:public,friends',
        ];

        foreach (array_keys(User::PRIVACY_DEFAULTS) as $section) {
            $rules['privacySettings.' . $section] = 'required|in:' . implode(',', User::PRIVACY_VISIBILITY_OPTIONS);
        }

        $this->validate($rules);

        $sanitizedPrivacy = array_intersect_key($this->privacySettings, User::PRIVACY_DEFAULTS);
        $this->privacySettings = array_merge(User::PRIVACY_DEFAULTS, $sanitizedPrivacy);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'profile_visibility' => $this->profile_visibility,
            'posts_visibility' => $this->posts_visibility,
            'privacy_settings' => $this->privacySettings,
            'notification_preferences' => $this->notificationPreferences,
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
            'password' => Hash::make($this->password)
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
        $this->notificationPreferences[$type] = !$this->notificationPreferences[$type];
    }

    public function render()
    {
        return view('livewire.user-settings', [
            'twoFactorEnabled' => auth()->user()->two_factor_enabled,
            'privacySections' => $this->privacySections(),
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
}
