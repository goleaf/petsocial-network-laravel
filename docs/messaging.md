# Real-Time Messaging

The social feed now includes a dedicated **Messages** area where friends can hold private, real-time conversations without ever leaving the app.

## Key Behaviours
- **Private channels** – every conversation uses a `chat.{userId}` private broadcast channel so only the authenticated participant receives updates.
- **Instant delivery** – new messages are pushed over Laravel Echo/Pusher and rendered live inside the thread without refreshing the page.
- **Read receipts** – as soon as a participant views an unread message the UI calls the new `markMessagesAsRead` helper, which updates the database and broadcasts a `MessageRead` event back to the original sender.
- **Historical sync** – Livewire still hydrates the entire conversation whenever you switch threads, ensuring state stays aligned with persisted history.

## Operational Notes
- Ensure environment variables for Pusher are configured locally so Laravel Echo can negotiate the websocket connection.
- When developing locally without websockets you can still trigger read receipts by invoking `markMessagesAsRead` via the component inspector or a Livewire action.
- The accompanying Pest test (`tests/Feature/Messaging/ReadReceiptsTest.php`) verifies that read acknowledgements persist and the broadcast is dispatched, keeping regressions obvious.
