<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Tag;

class TrendingTags extends Component
{
    public $trendingTags;

    public function mount()
    {
        $this->loadTrendingTags();
    }

    public function loadTrendingTags()
    {
        $this->trendingTags = Tag::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.trending-tags');
    }
}
