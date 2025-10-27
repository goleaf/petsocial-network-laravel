<?php

namespace App\Http\Livewire\Landing;

use App\Models\Pet;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

class HomePage extends Component
{
    /**
     * Aggregate metrics and curated post previews that power the landing page.
     */
    public array $stats = [];

    /**
     * Top community stories highlighted on the landing page hero.
     */
    public array $featuredPosts = [];

    /**
     * Prepare the landing page data and redirect authenticated users to the dashboard.
     */
    public function mount(): void
    {
        if (auth()->check()) {
            // Preserve the existing application flow by sending signed-in members to their dashboard.
            $this->redirectRoute('dashboard');

            return;
        }

        $this->loadStats();
        $this->loadFeaturedPosts();
    }

    /**
     * Build the headline metrics for the hero section using lightweight aggregate queries.
     */
    protected function loadStats(): void
    {
        $this->stats = [
            [
                'label' => 'Active Members',
                'value' => User::count(),
                'icon' => 'users',
            ],
            [
                'label' => 'Registered Pets',
                'value' => Pet::count(),
                'icon' => 'paw',
            ],
            [
                'label' => 'Community Posts',
                'value' => Post::count(),
                'icon' => 'book',
            ],
        ];
    }

    /**
     * Collect the three posts with the highest engagement to surface on the landing page.
     */
    protected function loadFeaturedPosts(): void
    {
        $posts = Post::query()
            ->with(['pet', 'user', 'tags'])
            ->withCount('reactions')
            ->latest('reactions_count')
            ->latest()
            ->take(3)
            ->get();

        $this->featuredPosts = $posts->map(function (Post $post): array {
            // Determine if the related pet has a stored avatar we can safely surface.
            $imagePath = null;
            if ($post->pet && $post->pet->avatar) {
                $avatarPath = $post->pet->avatar;

                if (Storage::disk('public')->exists($avatarPath)) {
                    $imagePath = Storage::url($avatarPath);
                }
            }

            // Provide rich metadata used in the Blade template to mirror the referenced design.
            return [
                'id' => $post->id,
                'title' => Str::limit(strip_tags($post->content), 70, '…'),
                'excerpt' => Str::limit(strip_tags($post->content), 160, '…'),
                'pet_name' => $post->pet?->name ?? $post->user?->name ?? 'Community Member',
                'likes' => $post->reactions_count,
                'tags' => $post->tags->pluck('name')->take(2)->values()->all(),
                'image' => $imagePath,
            ];
        })->toArray();
    }

    /**
     * Render the landing page using the dedicated Livewire-first layout.
     */
    public function render()
    {
        return view('livewire.landing.home-page', [
            'ctaHeadline' => 'Ready to Join the Community?',
            'ctaMessage' => 'Create your account today and start sharing your pet’s adventures.',
        ])->layout('layouts.landing', [
            'pageTitle' => 'Pet Social Network',
        ]);
    }
}
