<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Component EditProfile handles interactive updates to profile media and metadata.
 */
class EditProfile extends Component
{
    use WithFileUploads;

    /**
     * Stores the biography content entered by the user.
     *
     * @var string|null
     */
    public $bio;

    /**
     * Holds the existing avatar path for preview and cleanup.
     *
     * @var string|null
     */
    public $avatar;

    /**
     * Maintains the current cover photo path displayed in the UI.
     *
     * @var string|null
     */
    public $coverPhoto;

    /**
     * Temporarily stores the uploaded avatar file before persistence.
     *
     * @var mixed
     */
    public $newAvatar;

    /**
     * Temporarily stores the uploaded cover photo file before persistence.
     *
     * @var mixed
     */
    public $newCoverPhoto;

    /**
     * Keeps track of the user's declared location.
     *
     * @var string|null
     */
    public $location;

    /**
     * Seed the component state with the authenticated user's existing profile information.
     */
    public function mount(): void
    {
        $profile = auth()->user()->profile;
        $this->bio = $profile->bio;
        $this->avatar = $profile->avatar;
        $this->coverPhoto = $profile->cover_photo;
        $this->location = $profile->location;
    }

    /**
     * Persist updated profile details, handling optional media uploads safely.
     */
    public function updateProfile(): void
    {
        $data = $this->validate([
            'bio' => 'nullable|string|max:1000',
            'newAvatar' => 'nullable|image|max:2048',
            'newCoverPhoto' => 'nullable|image|max:4096',
            'location' => 'nullable|string|max:100',
        ]);

        $profile = auth()->user()->profile;

        if ($this->newAvatar) {
            $avatarPath = $this->newAvatar->store('avatars', 'public');
            if ($this->avatar) {
                Storage::disk('public')->delete($this->avatar);
            }
            $data['avatar'] = $avatarPath;
            $this->avatar = $avatarPath;
        }

        if ($this->newCoverPhoto) {
            $coverPath = $this->newCoverPhoto->store('covers', 'public');
            if ($this->coverPhoto) {
                Storage::disk('public')->delete($this->coverPhoto);
            }
            $data['cover_photo'] = $coverPath;
            $this->coverPhoto = $coverPath;
        }

        unset($data['newAvatar'], $data['newCoverPhoto']);

        $profile->update($data);

        session()->flash('message', __('profile.profile_updated'));
    }

    /**
     * Render the edit profile view within the application layout.
     */
    public function render(): View
    {
        return view('livewire.edit-profile')->layout('layouts.app');
    }
}
