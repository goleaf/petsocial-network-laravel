# Group Livewire Guidelines

- Keep membership status transitions (`pending`, `active`, `banned`) routed through helper methods like `Group::clearUserCache()` to preserve cache integrity.
- When extending moderation tooling ensure new actions emit descriptive session flashes so operators receive feedback in the UI.
- Maintain inline comments describing business rules inside Livewire components to satisfy the repository-wide commenting requirement.
