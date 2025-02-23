# pets-social-network
A Laravel-based social network for pet owners, featuring private and group chats, a news feed with posts, friend and follow systems, forum-like groups with events and polls, customizable notifications, and user settings. Built with Livewire for real-time interactions, Pusher for WebSocket updates, and a robust backend for scalability.


# Roadmap

## Features

### User Management
- **Profile**: Edit profile details (photo, bio, location).
- **Privacy Settings**: Public, friends-only, or private visibility.
- **Authentication**: Password updates and two-factor authentication (2FA).
- **Account Actions**: Deactivate or delete accounts.

### Friends and Follow System
- **Friends**: Send/accept friend requests, categorize friends, remove friends in bulk.
- **Follow**: Follow/unfollow users with notifications.
- **Recommendations**: Suggest nearby friends based on location.

### Posts and Feed
- **News Feed**: View posts from friends and followed users.
- **Posts**: Create text-based posts with likes and comments.
- **Interactions**: Like posts and add comments.

### Private Chat
- **One-on-One Messaging**: Real-time private chat with file attachments (images, videos).
- **Read Status**: Track when messages are read.
- **Notifications**: Alerts for new messages.

### Groups (Forum-like)
- **Creation**: Create groups (open, closed, secret) with descriptions and categories.
- **Topics**: Post topics with nested replies, polls, and attachments.
- **Events**: Schedule events with calendar export and social media publishing (Twitter, Facebook, Telegram).
- **Chat**: Real-time group chat with emoji support.
- **Moderation**: Pin/lock topics, ban users, bulk delete, handle content reports.
- **Subscriptions**: Customizable notifications for new topics, replies, pinned topics, and events.
- **Analytics**: Activity stats with Chart.js graphs.
- **Ratings**: Participant points and achievements.
- **Resources**: Attach links or PDFs to topics and events.

### Notifications
- **Multi-Channel**: Database, email, and push notifications.
- **Categories**: Messages, friend requests, posts, events, etc., with priorities.
- **Grouping**: Combine similar notifications for a cleaner UI.
- **Customization**: Per-category preferences.

### Technical Stack
- **Backend**: Laravel 10.x
- **Frontend**: Livewire for reactive components, Blade templates
- **Real-Time**: Laravel Echo with Pusher
- **Database**: MySQL (via migrations)
- **Libraries**: Chart.js (analytics), Socialite (social media), iCal (event export), emoji-picker-element (emojis)

## Prerequisites
- PHP 8.1+
- Composer
- Node.js & npm
- MySQL
- Pusher account (for real-time features)
- Social media API keys (Twitter, Facebook, Telegram) for event publishing

## Installation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/yourusername/PetSocialNetwork.git
   cd PetSocialNetwork
