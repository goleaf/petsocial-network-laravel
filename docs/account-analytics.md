# Account Analytics Dashboard

Authenticated users can now open **Account → Analytics** to review engagement metrics without leaving the application. The dashboard is powered by the `App\Http\Livewire\Account\Analytics` component and supports fully customisable reporting windows.

## Metrics Collected
- **Overview cards** – total posts, comments, reactions made, reactions received, and shares received.
- **Friend insights** – total accepted friends, new friends added in the last 30 days (derived from `accepted_at` when available), pending requests, and blocked connections.
- **Overview cards** – total posts, comments, reactions made, reactions received (excluding self-reactions), and shares received (excluding self-shares).
- **Friend insights** – total accepted friends, new friends added in the last 30 days, pending requests, and blocked connections.
- **Engagement trend** – six-month breakdown of posts, reactions received, and shares to illustrate momentum.
- **Top posts** – the five posts with the highest reaction counts for quick wins and inspiration.
- **Activity patterns** – weekday and hourly breakdowns highlight peak posting and interaction periods.
- **Behavior analysis** – ratios such as reactions, comments, and shares per post surface engagement preferences.
- **Growth tracking** – monthly friend and follower gains illuminate audience momentum.
- **Report summary** – mirrors the export payload so members can preview metrics before downloading.

## Filters & Exports
- Members can set start/end dates at the top of the dashboard; datasets automatically refresh when either input changes.
- The **Export report** button streams a CSV assembled by `exportReport()` which relies on Laravel's `streamDownload` helper.
- The exported CSV respects the current date range and includes every metric listed in the report summary card.

## Permissions
- Members require the `analytics.view_self` permission (granted to the `user` role by default) to access their personal dashboard.
- Moderators and administrators automatically receive `analytics.view`, enabling oversight of analytics tooling in other contexts.

## Extending the Dashboard
- Use the `buildOverview`, `buildEngagementTrend`, and `buildFriendInsights` helper methods for additional datasets. The friend insight builder automatically excludes pending/blocked relationships by reading the statuses normalised in `FriendshipTrait`.
- Use the `buildOverview`, `buildEngagementTrend`, `buildFriendInsights`, `buildActivityPatterns`, `buildBehaviorAnalysis`, and `buildGrowthTracking` helper methods for additional datasets.
- All calculations are performed using Eloquent queries so they remain database agnostic.
- When introducing new metrics, remember to update the localisation files (`resources/lang/*/common.php`) so the UI stays fully translated.

## Testing
- `tests/Feature/AccountAnalyticsAccessTest.php` covers permission gates, dataset aggregation, and CSV export streaming.
