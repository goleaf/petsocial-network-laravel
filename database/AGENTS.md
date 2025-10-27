# Database Contribution Guide

- Rely exclusively on Laravel migrations for schema changes; schema dump files have been removed.
- Keep seeders in sync with migrations so `php artisan migrate:fresh --seed` succeeds locally and in CI.
- Add PHPDoc comments when introducing new migration helpers to satisfy the house rule about commented code.
