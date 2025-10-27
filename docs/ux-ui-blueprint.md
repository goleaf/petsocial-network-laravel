# UX & UI Blueprint

This blueprint translates the product requirements into a cohesive experience architecture for the pet social network. Each section outlines navigation, primary screens, state handling, and accessibility expectations so designers and engineers can deliver consistent interfaces.

## 1. Accounts & Authentication
- **Entry Points:**
  - `/register`, `/login`, `/password/reset`, `/two-factor-challenge`, and dedicated OAuth redirect routes for Google, Apple, and Facebook.
  - Contextual links appear within the global header and any unauthenticated guard pages.
- **Layout Considerations:**
  - Use a centered card with brand illustration and `<x-icons.paw>` to align with onboarding visuals.
  - Support dark mode and reduced motion; transitions should fade without parallax when `prefers-reduced-motion` is detected.
- **Flows:**
  - Signup/login include floating label inputs, password strength indicators, and inline validation messaging.
  - Password reset and change flows share the same progress indicator (`1. Request → 2. Verify → 3. Confirm`).
  - Social sign-in buttons include provider colors with accessible contrast and icon glyphs.
  - Two-factor challenge supports TOTP entry and SMS fallback, with a "Trust this device" checkbox.
  - Session management lists active sessions with device/browser metadata and revoke controls.
  - Device list mirrors the session list but adds friendly names and last trusted timestamps.
  - Captcha or visual puzzle appears on final submission for signup and password reset when risk scores demand it.
  - Rate-limited states show toast feedback and disable primary actions until timers expire.

## 2. User Profiles & Identity
- **Profile Shell:**
  - Banner hero with gradient overlay, avatar overlapping at bottom-left, and quick actions (Follow, Message, More menu).
  - Stats row (followers, following, posts, pets) surfaces below hero; each metric links to dedicated modals with tabbed navigation.
- **Identity Elements:**
  - Display verification badge next to display name when approved.
  - Pronouns appear subtlety next to username with muted color.
  - Bio, location, and website blocks follow markdown-lite styling with auto-linking.
- **Privacy Controls:**
  - If profile is followers-only, display lock illustration with CTA to request follow.
  - Block/mute options live within a kebab menu that persists across breakpoints.
- **Social Graph:**
  - Import contacts uses a guided modal with progressive disclosure for email/phone uploads.
  - Linked networks show icon buttons with tooltips and `rel="me"` for verification.

## 3. Pet Profiles & Management
- **Pet Switcher:**
  - Owner dashboard includes a pet selector dropdown with avatars and quick add.
- **Profile Layout:**
  - Similar hero treatment with species/breed chips, adoptable badge, and lost & found toggle.
  - Health log accessible via private tab; surfaces timeline cards with weight charts and medication reminders.
  - Co-owner list displays avatar stack with role tooltips and manage access button.
  - Tag chips filter posts/gallery to highlight content.
  - Share CTA copies OG tag friendly URLs.

## 4. Social Graph & Relationships
- **Follow UX:**
  - Follow buttons toggle states with micro-interaction (paw pulse) and accessible text updates.
  - Private profiles trigger modal to confirm follow request.
- **Lists & Collections:**
  - Custom lists use grid cards with cover images, description, and member count.
  - Block/mute panels accessible from settings with search/filter.

## 5. Content Types
### 5.1 Short Posts
- Composer features resizable text area with character counter, attachment tray, tagging, and hashtag suggestions.
- Attachment carousel previews images (1–10) with reorder drag handles and video duration labels.
- Drafts autosave indicator pulses in toolbar; scheduled posts show clock badge.
- Poll builder displays up to 4 options with emoji support.
- Stories accessible via top rail with circular avatars and upload CTA.

### 5.2 Blogs
- Blog editor uses tabbed interface (Write/Preview) with markdown helper palette.
- Cover image dropzone supports drag-and-drop with cropper.
- Associate-to-pet selector uses searchable dropdown showing pet avatars.
- View page includes reading progress bar along right gutter and table of contents floating button.

### 5.3 Wiki
- Category index uses masonry cards with color-coded categories.
- Article page features sticky sidebar for tags/search; suggest-edit button leads to modal with form wizard.
- Moderator approval queue accessible via admin dashboard > Wiki tab.

### 5.4 Comments & Threads
- Comments display nested indentation with connecting lines; reply composer inline with mention autocomplete.
- Grace-period edit banner appears above comment with countdown.
- GIF picker accessible via attachments button with accessible search field.

### 5.5 Reactions & Collections
- Reaction bar uses horizontal pill buttons; long-press on mobile expands reaction picker.
- Bookmark toggles into "Saved" collection; share opens bottom sheet on mobile.
- Repost/Quote displays modal with original content preview.

## 6. Feeds & Discovery
- Home feed defaults to blended relevance; chronological toggle sits inline with feed header.
- Trending feed includes filters for time window and content type.
- Explore page uses segmented control tabs (Users, Pets, Blogs, Wiki) with persistent search input and filter drawer.
- Search results show vertical feed with sticky filters for species, breed, tags; saved searches accessible from header dropdown.
- Dedicated tag search page (`/tags`) relies on a paginated Livewire component that honours block lists and friends-only visibility rules when presenting results.
- Infinite scroll uses intersection observer with skeleton loaders; "Why am I seeing this?" reveals explanation drawer referencing mutual follows or tags.

## 7. Notifications
- Notification center accessible via bell icon with unread badge; supports tab filters (All, Mentions, Follows, System).
- List items group aggregated events with expander to view individual details.
- Settings modal allows toggling in-app, email, push per notification type.

## 8. Messaging & Community
- Direct messages open slide-over panel on desktop and full screen on mobile.
- Conversation list shows presence indicators and request queue.
- Group chats display participant pills and pinned messages.
- Clubs/Groups area integrates events tab and moderation tools in sidebar.

## 9. Events
- Event creation wizard spans Details, Schedule, Visibility, and Confirmation steps.
- Map picker uses Leaflet with dark/light tiles; RSVP list surfaces attendee avatars and role badges.

## 10. Marketplace & Adoption
- Adoption listings appear as cards with shelter info, distance, and contact CTA.
- Services marketplace uses filterable list with rating stars and booking links.
- Product listings highlight affiliate disclosures and price comparisons.
- Payment prompts integrate modal with saved cards and tipping slider.

## 11. Safety, Reporting & Moderation
- Report flow uses stepper: Category → Details → Preview → Submit.
- Moderation inbox accessible via admin dashboard with split view (queue list + detail pane).
- Auto-filter hits show warning banners with override options for moderators.

## 12. Privacy Controls
- Privacy settings page offers accordion sections (Profile, Posts, Location, Data) with toggles and info tooltips.
- Per-post audience selector sits next to publish button (Public, Followers, Custom list).
- Location privacy includes map blur preview illustrating reduced precision.
- Download data and delete account actions require re-auth confirmation modal.

## 13. Settings
- Settings uses left navigation rail with sections (Profile, Account, Privacy, Notifications, Security, Appearance, Language).
- Each pane uses cards with inline descriptions and action buttons.
- Security tab lists sessions, devices, 2FA status, and recent login alerts.
- Appearance supports light/dark/high-contrast preview thumbnails.

## 14. Admin & Operations
- Admin dashboard landing displays KPI cards (new users, reports, active sessions) with filters.
- Moderation queue includes bulk actions, audit log timeline, and feature flag toggles.
- Taxonomy manager offers drag-and-drop ordering and inline editing.

## 15. Analytics & Growth
- Analytics surfaces event tracking plan cards with export button.
- Creator analytics shows charts (line, bar) via responsive SVG with accessible descriptions.
- Funnel dashboards use stepper visualization; SEO tools provide OG preview cards and sitemap status.
- Referral center includes shareable invite link, QR code, and progress tracker.

## 16. Internationalization & Accessibility
- Language switcher lives in footer and settings; uses ISO language names.
- RTL support flips layout with mirrored icons and navigation order.
- Enforce WCAG AA: color contrast tokens, focus outlines, skip link at top of page, alt text prompts on uploads, caption guidance for video.
- Reduced motion toggles disable background animations.
- Automated Feature, HTTP, Unit, and Livewire tests guard the language switcher so locale persistence stays reliable when flows evolve.

## 17. Performance & Reliability
- Media surfaces display responsive image sizes with skeleton placeholders.
- Video components show adaptive bitrate indicator and fallback download link.
- Feed virtualization reuses windowing component shared across lists.
- Error boundaries display friendly paw illustration with retry button; offline mode uses toast and queue icon.

## 18. Security
- Input components integrate validation states with icons, ARIA live regions, and sanitized previews for markdown fields.
- CSRF-protected forms include subtle status indicator when tokens refresh.
- Secrets and permission checks documented in developer console accessible to admins only.
- Audit logs display timeline with filters by severity and actor.

## 19. Data Model Highlights
- Provide inline tooltips in settings to explain entities (e.g., Post vs. Story).
- Relationship diagrams accessible via admin docs page referencing ERD.

## 20. API Surface
- Developer portal lists REST endpoints with example requests, code samples, and rate-limit badge.
- API keys management includes scopes, last used timestamps, and revoke buttons.

## 21. Storage & Media
- Upload dialogs support drag-and-drop, clipboard paste, and mobile camera capture.
- Progress bars show upload status; success states prompt for alt text and tagging before publish.
- Virus scan status indicates pending/approved with icon legend.

## 22. Observability & Operations
- Admin status page surfaces uptime chart, incident history, and maintenance mode toggle.
- Alerts tab lists PagerDuty-style notifications with acknowledgment workflow.

---

### Navigation Summary
- **Primary Nav:** Home, Explore, Messages, Notifications, Create (+), Profile menu.
- **Secondary Nav:** For authenticated users, quick switch between personal and pet profiles via avatar stack.
- **Global Search:** Persistent search bar with auto-complete.
- **Floating Actions:** Mobile surfaces floating paw button for creating posts, stories, events, or adoption listings.

### Guest Landing Page
- **Livewire-first layout:** The `/` route now renders the `App\\Http\\Livewire\\Landing\\HomePage` component with a dedicated `landing` layout so content stays interactive without JavaScript controllers.
- **Hero metrics:** Display cards pull aggregate counts for users, pets, and posts to mirror the V0 hero statistics. Update `HomePage::loadStats()` whenever new headline metrics are required.
- **Trending stories:** The component surfaces the three posts with the highest reaction totals, including tags and like counts, creating parity with the "Trending Stories" grid from the reference design.
- **Call to action:** A full-width CTA card closes the page with prominent register and sign-in buttons; keep wording aligned with marketing copy when iterating.

### Design System Notes
- Adopt existing Tailwind tokens for colors/spacing; define component patterns for cards, modals, tabs, and toasts.
- Provide high-contrast mode variant tokens meeting WCAG 2.1 AA.
- Maintain component documentation in Storybook-style gallery (future enhancement) to keep parity across web/mobile clients.

