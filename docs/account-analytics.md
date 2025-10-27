# Account Analytics Dashboard

Authenticated users can now open **Account → Analytics** to review engagement metrics without leaving the application. The dashboard is powered by the new `App\Http\Livewire\Account\Analytics` component.

## Metrics Collected
- **Overview cards** – total posts, comments, reactions made, reactions received, and shares received.
- **Friend insights** – total accepted friends, new friends added in the last 30 days, pending requests, and blocked connections.
- **Engagement trend** – six-month breakdown of posts, reactions received, and shares to illustrate momentum.
- **Top posts** – the five posts with the highest reaction counts for quick wins and inspiration.

## Permissions
- Members require the `analytics.view_self` permission (granted to the `user` role by default) to access their personal dashboard.
- Moderators and administrators automatically receive `analytics.view`, enabling oversight of analytics tooling in other contexts.

## Extending the Dashboard
- Use the `buildOverview`, `buildEngagementTrend`, and `buildFriendInsights` helper methods for additional datasets.
- All calculations are performed using Eloquent queries so they remain database agnostic.
- When introducing new metrics, remember to update the localisation files (`resources/lang/*/common.php`) so the UI stays fully translated.

## Testing
- `tests/Feature/AccountAnalyticsAccessTest.php` ensures only members with the correct permissions can mount the dashboard.
