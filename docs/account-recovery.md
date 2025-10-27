# Account Recovery Workflow

The account recovery flow now captures every password-reset request in the `account_recoveries` table so that operators and automated tooling have a complete audit trail.

## Password Reset Requests
- When a visitor submits the "Forgot Password" form we log the request with the associated email, IP address, user agent, and timestamp.
- The status column stores `sent` when the broker dispatches the reset email and `failed` when the broker reports an error.
- The controller populates `user_id` when the email matches a known account so we can correlate recovery attempts with specific users.
- Local development captures password reset emails in the application log because the default mail transport uses the `log` driver, ensuring no outbound SMTP sockets are required.

## Completion Tracking
- The `LogPasswordReset` listener marks the most recent open recovery record as `completed` once the password reset succeeds.
- Administrators can review the audit history to detect suspicious patterns (multiple failures, repeated requests, etc.).

## Account Reactivation
- Manual reactivation events (`/account/reactivate`) are recorded in the activity log with severity information so support teams can verify when an account returns to active status.
- Combine the activity logs and recovery records when investigating account compromise reports.

## Testing
- `tests/Feature/AccountRecoveryLoggingTest.php` validates that every password reset request is persisted with status, metadata, and user linkage.
