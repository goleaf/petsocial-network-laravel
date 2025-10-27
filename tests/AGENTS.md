# Tests Directory Guidance

- New or updated tests must include inline comments that describe the arrange, act, and assert phases so the reasoning stays explicit.
- Reuse Pest for both feature and unit coverage; bind `uses(TestCase::class)` whenever a test relies on the Laravel container or helpers.
- CSRF protection coverage now lives under `Feature/VerifyCsrfTokenFeatureTest.php`, `Feature/Http/VerifyCsrfTokenHttpTest.php`, `Feature/Livewire/VerifyCsrfTokenLivewireTest.php`, and `Unit/Http/Middleware/VerifyCsrfTokenTest.php`. Keep these files updated whenever the middleware changes.
