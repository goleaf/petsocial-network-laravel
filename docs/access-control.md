# Access Control Overview

This project uses a lightweight role-based access control (RBAC) layer that is defined entirely in configuration. The new `config/access.php` file lists every supported role, the permissions it grants, and any inheritance chains. Roles can include wildcard permissions using the `.*` suffix to cover entire namespaces of abilities.

## Roles and Permissions
- **Administrator (`admin`)** – inherits every permission via the wildcard (`*`). This role is required for the admin panel and sensitive moderation tooling.
- **Moderator (`moderator`)** – inherits the standard member permissions and adds access to moderation queues (`moderation.*`) as well as read-only analytics dashboards.
- **Member (`user`)** – baseline role for all accounts with permissions to manage personal profiles, privacy settings, social relationships, and content publishing.

User models expose helper methods (`hasRole`, `hasPermission`, `permissions`) that read directly from the configuration, so updates in `config/access.php` propagate automatically across middleware, gates, and UI validation.

## Gate Integration
- `admin.access` – used by the admin middleware and gates to restrict the administrative area.
- `moderation.manage` – checks for the `moderation.*` permission block to keep moderation tooling consistent.
- `analytics.view` – allows access to personal analytics for members (`analytics.view_self`) and full analytics for moderators/administrators.

When adding new permissions, place them in the appropriate role configuration and reuse the helper methods on the `User` model to keep policy checks consistent.

## Testing
- `tests/Feature/RbacPermissionsTest.php` exercises the role helper methods so changes to `config/access.php` stay verifiable.
- Activity visibility for the social graph is now covered by a dedicated suite under `tests/Feature`, `tests/Livewire`, `tests/Http`, `tests/Filament`, and `tests/Unit`, ensuring the friend activity log respects privacy and caching rules across layers.

## Real-Time Chat Channels
- Private chat broadcasts use the `chat.{id}` channel namespace to ensure events stay scoped to authenticated participants.
- Channel authorization allows access for the matching user ID or accounts granted the `admin.access` permission so moderators can troubleshoot conversations without exposing messages broadly.
- JavaScript listeners subscribe via `Echo.private` which respects Laravel's `/broadcasting/auth` endpoint for authentication checks.

## Profile Privacy Presets
- The **Settings → Privacy Settings** panel now includes audience presets that instantly set every section to public, friends-only, or private.
- Presets call `App\Http\Livewire\UserSettings::applyPrivacyPreset()` which keeps the `privacy_settings` JSON column synchronised with `App\Models\User::PRIVACY_DEFAULTS`.
- Section visibility is enforced at render-time across profile pages, friend lists, and activity logs using `User::canViewPrivacySection()` so visitors see localized guidance whenever content is hidden.

## Group Membership Lifecycle
- Membership states within `group_members` now include `active`, `pending`, and `banned` values so moderation decisions feed directly into authorization checks.
- Closed and secret communities capture join attempts as `pending` records; once moderators approve the request the status flips to `active`, unlocking posts, topics, and analytics access without requiring duplicate records.

## Friendship Data Export
- Members with the `friends.manage` permission can export both user and pet relationships from the Friend Hub, ensuring the capability stays scoped to trusted accounts.
- Pet friendship exports rely on `App\Models\Pet::exportFriendsToCsv()`, `exportFriendsToJson()`, and `exportFriendsToVcf()` which normalise owner contact details alongside pet metadata.
- Generated files are stored on the public disk under `storage/app/public/exports` and surfaced via signed URLs so operators can retrieve CSV, JSON, or VCF packages as needed.

## Social Relationship Management
- **Bidirectional approvals** – friend requests now persist with a `pending` status until the recipient accepts, keeping relationships mutual by design. Accepted rows store an `accepted_at` timestamp when supported so analytics can build accurate timelines.
- **Request lifecycle tools** – members can send, accept, decline, cancel, or remove connections from the unified friendship controller. Activity entries keep the audit trail intact while cache busting ensures the UI reflects new states instantly.
- **Custom friend categories** – the `FriendshipTrait::categorizeFriends()` helper updates categories for both sides of a relationship, enabling groups such as Family or Close Friends to power privacy filters.
- **Mutual discovery & suggestions** – friend suggestions leverage cached mutual-friend calculations via `FriendshipTrait::getFriendSuggestions()`, excluding pending, blocked, and existing connections to improve accuracy.
- **Follow without reciprocity** – the `follows` table continues to support one-way following for users and pets so fans can subscribe without sending a friend request.
- **Comprehensive blocking** – blocking a user or pet promotes the relationship to the blocked state and removes associated cache entries, preventing renewed contact until explicitly unblocked.

## UX & UI Reference
- The **UX & UI Blueprint** (`docs/ux-ui-blueprint.md`) captures the presentation and interaction patterns for RBAC-driven surfaces, including moderation queues, admin dashboards, and relationship management screens. Review it alongside this guide when designing or updating permission-gated interfaces to ensure consistent affordances and accessibility.
## UI Notes
- The guest layout and welcome page now render the brand paw glyph through the reusable `<x-icons.paw>` component to keep iconography consistent across onboarding surfaces.
- The unified search experience now layers in saved searches, history, location filters, trending modules, and suggestions while still respecting role-based visibility and friend-only content rules.
- Social entry points reuse dedicated icon components such as `<x-icons.calendar>`, `<x-icons.share>`, and `<x-icons.download>` so future marketing experiments can depend on a stable asset catalog.
- Icon components now ship with safe default `stroke-width` values, so marketing and onboarding templates can override the stroke weight without triggering runtime warnings.
