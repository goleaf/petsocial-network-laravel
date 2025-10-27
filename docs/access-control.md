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

## Profile Privacy Presets
- The **Settings → Privacy Settings** panel now includes audience presets that instantly set every section to public, friends-only, or private.
- Presets call `App\Http\Livewire\UserSettings::applyPrivacyPreset()` which keeps the `privacy_settings` JSON column synchronised with `App\Models\User::PRIVACY_DEFAULTS`.
- Section visibility is enforced at render-time across profile pages, friend lists, and activity logs using `User::canViewPrivacySection()` so visitors see localized guidance whenever content is hidden.

## Friendship Data Export
- Members with the `friends.manage` permission can export both user and pet relationships from the Friend Hub, ensuring the capability stays scoped to trusted accounts.
- Pet friendship exports rely on `App\Models\Pet::exportFriendsToCsv()`, `exportFriendsToJson()`, and `exportFriendsToVcf()` which normalise owner contact details alongside pet metadata.
- Generated files are stored on the public disk under `storage/app/public/exports` and surfaced via signed URLs so operators can retrieve CSV, JSON, or VCF packages as needed.
