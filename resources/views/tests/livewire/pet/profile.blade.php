<div>
    <!-- Simplified PetProfile view leveraged only inside tests to avoid nested component dependencies. -->
    <h1>{{ $pet->name }}</h1>
    <p>{{ $friendCount }}</p>
    <p>{{ $isOwner ? 'owner' : 'viewer' }}</p>
</div>
