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
- `tests/Feature/Auth/AuthenticatedSessionControllerTest.php`, `tests/Http/Controllers/Auth/AuthenticatedSessionControllerHttpTest.php`, and `tests/Livewire/Auth/AuthenticatedSessionControllerLivewireTest.php` cover the authentication flow so login restrictions (deactivated or suspended accounts) and guard state propagation stay reliable across request types.

## CSRF Protection
- The CSRF guard lives in `App\\Http\\Middleware\\VerifyCsrfToken` with an empty `$except` array so every web route requires the token handshake.
- Dedicated regression tests validate the middleware across entry points:
  - `tests/Feature/VerifyCsrfTokenFeatureTest.php` asserts form posts without tokens are rejected while valid submissions succeed.
  - `tests/Feature/Http/VerifyCsrfTokenHttpTest.php` exercises JSON requests that use the `X-CSRF-TOKEN` header and `XSRF-TOKEN` cookie pairing.
  - `tests/Feature/Livewire/VerifyCsrfTokenLivewireTest.php` confirms Livewire components read and validate the session-backed token.
  - `tests/Unit/Http/Middleware/VerifyCsrfTokenTest.php` locks down the middleware configuration and dynamic exception registration API.

## Real-Time Chat Channels
- Private chat broadcasts use the `chat.{id}` channel namespace to ensure events stay scoped to authenticated participants.
- Channel authorization allows access for the matching user ID or accounts granted the `admin.access` permission so moderators can troubleshoot conversations without exposing messages broadly.
- JavaScript listeners subscribe via `Echo.private` which respects Laravel's `/broadcasting/auth` endpoint for authentication checks.

## Profile Privacy Presets
- The **Settings → Privacy Settings** panel now includes audience presets that instantly set every section to public, friends-only, or private.
- Presets call `App\Http\Livewire\UserSettings::applyPrivacyPreset()` which keeps the `privacy_settings` JSON column synchronised with `App\Models\User::PRIVACY_DEFAULTS`.
- Section visibility is enforced at render-time across profile pages, friend lists, and activity logs using `User::canViewPrivacySection()` so visitors see localized guidance whenever content is hidden.

## Pet Profile Visibility Guardrails
- Pet profile pages rely on `App\Http\Livewire\Pet\PetProfile` to abort with a 403 whenever a private pet is viewed by a non-owner, ensuring animal data respects owner intent.
- The component primes caches for the profile payload and friend counts via `Cache::remember`, which is verified by dedicated Feature, Unit, Livewire, Filament-alias, and HTTP tests in `tests/Feature/PetProfileFeatureTest.php`, `tests/Unit/PetProfileTest.php`, `tests/Livewire/PetProfileComponentTest.php`, `tests/Filament/PetProfileFilamentTest.php`, and `tests/Http/PetProfileHttpTest.php`.

## Group Membership Lifecycle
- Membership states within `group_members` now include `active`, `pending`, and `banned` values so moderation decisions feed directly into authorization checks.
- Closed and secret communities capture join attempts as `pending` records; once moderators approve the request the status flips to `active`, unlocking posts, topics, and analytics access without requiring duplicate records.
- The group settings Livewire component now has layered Feature, Unit, Livewire, Filament, and HTTP test coverage so visibility and category changes stay reliable during future refactors.

## Group Role Permissions
- Every group seeds `Admin`, `Moderator`, and `Member` role definitions via `App\Models\Group\Group::ensureDefaultRoles()`. Each blueprint carries colour coding, descriptions, and curated permission lists that mirror the defaults previously provided by the database seeders.
- The helper `Group::syncMemberRole($user, $roleKey, $overrides = [])` updates the `group_members` pivot, refreshes cached membership checks, and syncs the related `group_user_roles` bridge so permission lookups always reference the correct `group_roles` record.
- Livewire flows now call `syncMemberRole` when creating groups, approving join requests, and reassigning moderators. The bridge helpers are exercised in `tests/Feature/GroupManagementIndexFeatureTest.php` and `tests/Livewire/GroupDetailsShowComponentTest.php`, ensuring admin promotions and moderator assignments attach the appropriate permission payloads.

## Friendship Data Export
- Members with the `friends.manage` permission can export both user and pet relationships from the Friend Hub, ensuring the capability stays scoped to trusted accounts.
- Pet friendship exports rely on `App\Models\Pet::exportFriendsToCsv()`, `exportFriendsToJson()`, and `exportFriendsToVcf()` which normalise owner contact details alongside pet metadata.
- Generated files are stored on the public disk under `storage/app/public/exports` and surfaced via signed URLs so operators can retrieve CSV, JSON, or VCF packages as needed.
- Livewire friend exports now include automated coverage across formats and entry points (`tests/Feature/FriendExportFeatureTest.php`, `tests/Feature/FriendExportLivewireTest.php`, `tests/Feature/Http/FriendExportHttpTest.php`, `tests/Feature/Filament/FriendExportFilamentTest.php`, and `tests/Unit/Common/Friend/ExportFormattingTest.php`) so regressions are detected quickly during CI.
- The suite now also validates that the `livewire.common.friend.export` blade template remains registered and wired to the component across HTTP, Livewire, Feature, and Unit contexts, guarding against rendering regressions when refactoring the export workflow.

## Pet Medical Records Access
- The private medical records dashboard lives at the authenticated route `pets/medical-records/{pet}` and mounts the `App\Http\Livewire\Pet\MedicalRecords` component.
- Authorization is enforced within `MedicalRecords::mount()` by comparing the pet owner to the current user, preventing friends or moderators from viewing sensitive health data unless they own the pet profile.
- Feature and HTTP tests under `tests/Feature/PetMedicalRecordsFeatureTest.php` and `tests/Http/PetMedicalRecordsHttpTest.php` guard this behaviour to ensure future UI updates do not bypass the access check.

## Social Relationship Management
- **Bidirectional approvals** – friend requests now persist with a `pending` status until the recipient accepts, keeping relationships mutual by design. Accepted rows store an `accepted_at` timestamp when supported so analytics can build accurate timelines.
- **Request lifecycle tools** – members can send, accept, decline, cancel, or remove connections from the unified friendship controller. Activity entries keep the audit trail intact while cache busting ensures the UI reflects new states instantly.
- **Custom friend categories** – the `FriendshipTrait::categorizeFriends()` helper updates categories for both sides of a relationship, enabling groups such as Family or Close Friends to power privacy filters.
- **Mutual discovery & suggestions** – friend suggestions leverage cached mutual-friend calculations via `FriendshipTrait::getFriendSuggestions()`, excluding pending, blocked, and existing connections to improve accuracy.
- **Follow without reciprocity** – the `follows` table continues to support one-way following for users and pets so fans can subscribe without sending a friend request.
- **Follower discovery UI** – the Livewire `Common\\Follow\\FollowList` component now powers the `/followers` route with searchable, paginated results so operators can audit or support communities efficiently.
- **Comprehensive blocking** – blocking a user or pet promotes the relationship to the blocked state and removes associated cache entries, preventing renewed contact until explicitly unblocked.
- **Test coverage** – the Livewire friendship button now includes unit, feature, HTTP, Livewire, and Filament-style tests so the entire request lifecycle stays stable as new UX hooks are introduced.
- **Follow button blade verification** – the component tests now assert that `App\\Http\\Livewire\\Common\\Follow\\Button` renders the `livewire.common.follow.button` Blade view across Livewire, Feature, Unit, and HTTP suites to guard against accidental view regressions.
- **Block button coverage** – automated Feature, Unit, Livewire, Filament-simulation, and HTTP tests under `tests/*/Common/User/BlockButton*` now guarantee the UI toggle faithfully reflects the blocks pivot table.

## UX & UI Reference
- The **UX & UI Blueprint** (`docs/ux-ui-blueprint.md`) captures the presentation and interaction patterns for RBAC-driven surfaces, including moderation queues, admin dashboards, and relationship management screens. Review it alongside this guide when designing or updating permission-gated interfaces to ensure consistent affordances and accessibility.
## UI Notes
- The guest layout and welcome page now render the brand paw glyph through the reusable `<x-icons.paw>` component to keep iconography consistent across onboarding surfaces.
- The unified search experience now layers in saved searches, history, location filters, trending modules, and suggestions while still respecting role-based visibility and friend-only content rules.
- Social entry points reuse dedicated icon components such as `<x-icons.calendar>`, `<x-icons.share>`, and `<x-icons.download>` so future marketing experiments can depend on a stable asset catalog.
- Icon components now ship with safe default `stroke-width` values, so marketing and onboarding templates can override the stroke weight without triggering runtime warnings.

## Safety, Reporting & Moderation Tests
- Automated coverage validates the Livewire `content.report-post` component across feature, unit, HTTP, Livewire, and Filament perspectives (`tests/*/Content/ReportPost*Test.php`) so moderation tooling stays resilient during refactors.
