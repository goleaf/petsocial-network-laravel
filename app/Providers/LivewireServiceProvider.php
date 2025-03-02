<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Http\Livewire\Social\Friend;
use App\Http\Livewire\Social\Follow;
use App\Http\Livewire\Admin;
use App\Http\Livewire\Group;
use App\Http\Livewire\Content;
use App\Http\Livewire\Pet;

class LivewireServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Friend components with shorter aliases
        Livewire::component('social.friend.activity', Friend\Activity::class);
        Livewire::component('social.friend.analytics', Friend\Analytics::class);
        Livewire::component('social.friend.button', Friend\Button::class);
        Livewire::component('social.friend.dashboard', Friend\Dashboard::class);
        Livewire::component('social.friend.export', Friend\Export::class);
        Livewire::component('social.friend.finder', Friend\Finder::class);
        Livewire::component('social.friend.list', Friend\List::class);
        Livewire::component('social.friend.requests', Friend\Requests::class);
        Livewire::component('social.friend.suggestions', Friend\Suggestions::class);
        
        // Register Follow components with shorter aliases
        Livewire::component('social.follow.button', Follow\Button::class);
        Livewire::component('social.follow.list', Follow\List::class);
        
        // Register Admin components with shorter aliases
        Livewire::component('admin.dashboard', Admin\Dashboard::class);
        Livewire::component('admin.analytics', Admin\Analytics::class);
        Livewire::component('admin.manage-users', Admin\ManageUsers::class);
        
        // Register Group components with shorter aliases
        Livewire::component('group.detail', Group\Detail::class);
        Livewire::component('group.management', Group\Management::class);
        Livewire::component('group.topics', Group\Topics::class);
        
        // Register Content components with shorter aliases
        Livewire::component('content.comment-section', Content\CommentSection::class);
        Livewire::component('content.create-post', Content\CreatePost::class);
        Livewire::component('content.like-button', Content\LikeButton::class);
        Livewire::component('content.reaction-button', Content\ReactionButton::class);
        Livewire::component('content.report-comment', Content\ReportComment::class);
        Livewire::component('content.report-post', Content\ReportPost::class);
        Livewire::component('content.share-button', Content\ShareButton::class);
        
        // Register Pet components with shorter aliases
        Livewire::component('pet.management', Pet\PetManagement::class);
        Livewire::component('pet.friends', Pet\PetFriends::class);
        Livewire::component('pet.profile', Pet\PetProfile::class);
        Livewire::component('pet.activity-log', Pet\ActivityLog::class);
    }
}
