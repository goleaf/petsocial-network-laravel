# Multi-Factor Authentication Overview

This project now supports enterprise-grade multi-factor authentication (MFA) that combines password verification with trusted device validation. The workflow below explains how the feature is implemented and how to operate it during development and testing.

## Enabling MFA
1. Sign in and open **Settings → Two-Factor Authentication**.
2. Click **Enable two-factor authentication** to generate a QR code, manual setup key, and recovery codes.
3. Scan the QR code with any TOTP authenticator (Google Authenticator, 1Password, Authy, etc.) or enter the setup key manually.
4. Submit the 6-digit code from the authenticator to confirm activation. Recovery codes are stored on the user record and shown once during setup.

## Trusted Devices
- After enabling MFA, every login checks for a `device_verification` cookie that references a hashed record in the new `user_devices` table.
- When the challenge form is submitted with **Trust this device**, a secure, random token is stored server-side and persisted in a `SameSite=Lax` cookie so repeat logins skip the MFA prompt.
- Users can review and revoke trusted devices at **Settings → Two-Factor Authentication → Trusted devices**. Removing a device deletes the database entry; the next login from that browser will require MFA again.
- Device cookies are validated automatically during login and normal browsing sessions—when the hashed token is recognised the middleware marks the session as verified, refreshes the device metadata, and clears stale cookies when no match is found.

## Recovery Codes
- Recovery codes are generated in batches of eight and saved on the user record.
- Each code is single-use. Upon successful validation, the used code is removed from storage to prevent reuse.

## Middleware Enforcement
- `EnsureTwoFactorIsVerified` enforces the MFA challenge for authenticated users with MFA enabled. Only the MFA challenge/verification routes and the logout route bypass the check.
- Recognized devices update their last-used timestamp and continue without a challenge, ensuring transparent yet secure logins.

## Database Artifacts
- Migration `2025_03_05_000001_create_user_devices_table` provisions the `user_devices` table for trusted device storage.
- All trusted-device columns now ride through standard Laravel migrations, so no schema dumps are required to mirror production locally.

## Testing Notes
- Automated tests can sign in without MFA by disabling the feature or by simulating a trusted device cookie that matches a seeded `user_devices` record.
- `php artisan test` currently reports that the `tests/Unit` directory is missing; add tests under `tests/Feature` to exercise MFA flows if needed.
