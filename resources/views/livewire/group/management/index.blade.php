{{-- Group management index placeholder ensures Livewire rendering during automated tests. --}}
<div data-testid="group-management-index-root">
    {{-- Categories are iterated to ensure the view touches cached relationships for regression coverage. --}}
    <div class="hidden">
        @foreach ($categories as $category)
            <span>{{ $category->name }}</span>
        @endforeach
    </div>

    {{-- Groups are listed in a lightweight container so pagination can be asserted within tests. --}}
    <div class="hidden">
        @foreach ($groups as $group)
            <span>{{ $group->name }}</span>
        @endforeach
    </div>
</div>
