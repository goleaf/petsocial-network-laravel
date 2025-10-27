<div>
    <!-- The group settings view keeps markup minimal so component-driven tests remain focused. -->
    <h1 class="text-xl font-semibold">Group Settings</h1>

    <ul class="mt-4 list-disc list-inside">
        <!-- Listing categories helps HTTP assertions confirm data is reaching the template. -->
        @foreach ($categories as $category)
            <li>{{ $category->name }}</li>
        @endforeach
    </ul>
</div>
