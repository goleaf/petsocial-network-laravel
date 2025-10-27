# UX/UI Style Guide

The PetSocial Network style guide consolidates the complete catalogue of reusable components and opinionated page patterns that power the product experience.

## Accessing the Guide
- Visit **`/ux/style-guide`** while authenticated to browse the interactive catalogue.
- The guide supports light and dark themes via the toggle in the global header.
- Alpine-driven demos show recommended behaviour for overlays, loaders, and other interactive primitives.

## Component Coverage
The guide contains canonical implementations for every critical component:
- **Buttons, Icon Buttons, FAB** – usage hierarchy, focus management, and floating action button treatment.
- **Inputs & Validation** – text, textarea, password, email inputs with masks, helper text, and inline validation.
- **Select, Multi-select, Combobox** – filtered search, chips, and keyboard-friendly selection states.
- **Chips/Tags** – removable tokens, filter toggles, and add-tag controls.
- **Date/Time Pickers** – single date, time, and range pickers with quick presets.
- **File Uploader** – drag-drop affordances, progress indicators, cancel, retry, and crop entry point.
- **Avatar with Status Ring** – presence indicators with live status rings.
- **Card & List Patterns** – feed cards and dense list rows with action affordances.
- **Tabs & Accordions** – responsive switching with collapsing sections for settings pages.
- **Modal, Drawer, Dropdown** – blocking and non-blocking overlays with focus trapping guidance.
- **Pagination & Infinite Scroll** – hybrid list navigation with load-more treatment.
- **Breadcrumbs** – hierarchical navigation trail for deep routes.
- **Search with Autocomplete** – suggestions, history, and command palette shortcut.
- **Toast & Snackbar** – stacked notifications with tone-specific palettes.
- **Tooltip & Hover Card** – micro-interactions for contextual detail.
- **Progress & Skeleton Loaders** – linear, circular, and skeleton placeholders.
- **Stepper (Onboarding)** – three-step onboarding scaffold with skip reminders.
- **Lightbox & Carousel** – responsive media viewer with keyboard-friendly navigation.
- **Markdown Renderer** – prose styling for blog and wiki content.
- **Emoji & GIF Picker** – dual-mode palette with recent selections.
- **Report & Confirmation Dialogs** – sensitive action workflows and follow-up messaging.

## Page Pattern Coverage
Each primary funnel and engagement screen includes a responsive wireframe:
- **Landing** – hero, value props, social proof/testimonials.
- **Auth** – inline errors, password strength, magic link option.
- **Onboarding** – profile → first pet → follow suggestions, progress indicator, skip reminder.
- **Feed** – composer, filters, sticky mobile CTA, post card blueprint.
- **Explore** – unified search with tabs, facets, trending tags, and follow CTAs.
- **Profile (User)** – cover actions, tabs, edit profile modal entry.
- **Pet** – cover, avatar, quick facts, owner controls, timeline/gallery tabs.
- **Blog** – index search/sort/tags, reader with table of contents and reading progress, reactions/sidebar suggestions.
- **Wiki** – category tabs, search, related articles sidebar.
- **Notifications** – unread badges, filters, bulk mark read.
- **Settings** – tabs for Profile/Account/Privacy/Notifications/Appearance plus danger zone.
- **Reporting & Moderation** – overflow-triggered report modal, confirmation and thank-you toast.
- **System** – 404 “We lost this pet!” CTA and error boundary retry flow.

## Maintenance Expectations
- Update this style guide and documentation whenever component styles, state handling, or interaction patterns change.
- Keep Tailwind classes aligned with project conventions and ensure dark-mode parity using `dark:` utilities.
- Coordinate changes with localisation strings if labels or CTA text evolve.
- Extend tests when new flows require backend validation or Livewire integration.

## Related Resources
- **Docs** – This page should be reviewed with `docs/access-control.md`, `docs/account-analytics.md`, `docs/account-recovery.md`, and `docs/multi-factor-authentication.md` when features overlap UI & policy.
- **AGENTS.md** – Includes project-wide conventions and the reminder to keep `/ux/style-guide` in sync.
