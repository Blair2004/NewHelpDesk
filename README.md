# Helpdesk Ticketing System

A production-grade multilingual ticketing system built with Laravel 12 and Vue 3.

## Features

- **OAuth Authentication**: Secure authentication with external OAuth provider
- **Multilingual Support**: AI-powered automatic translation of messages
- **Public & Private Threads**: Support for both public forum-style and private support tickets
- **Role-Based Access Control**: Comprehensive permission system with admin and agent roles
- **Real-time Chat Interface**: Modern, responsive chat-style UI
- **File Attachments**: Image upload support with drag-and-drop
- **Message Revisions**: Track message edit history (staff only)
- **Department Management**: Organize support by departments
- **Reports & Analytics**: Track performance and metrics

## Technology Stack

### Backend
- Laravel 12
- PHP 8.2+
- MySQL/PostgreSQL
- OAuth 2.0 Client
- Queue System (Database/Redis)

### Frontend
- Vue 3 (Composition API)
- Vite
- TailwindCSS
- Pinia (State Management)
- Vue Router
- Moment.js

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL or PostgreSQL
- Redis (optional, for queues)

### Steps

1. **Clone the repository**
   ```bash
   cd /var/www/html/helpdesk
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure environment variables**
   Edit `.env` and set:
   - Database credentials
   - OAuth credentials (CLIENT_ID, CLIENT_SECRET, URLs)
   - AI provider credentials (for translation)
   - Queue connection

6. **Run migrations**
   ```bash
   php artisan migrate --seed
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   ```

8. **Start queue worker** (in separate terminal)
   ```bash
   php artisan queue:work
   ```

9. **Start development server**
   ```bash
   php artisan serve
   ```

## Configuration

### OAuth Provider Setup
Configure your OAuth provider endpoints in `.env`:
```env
OAUTH_CLIENT_ID=your-client-id
OAUTH_CLIENT_SECRET=your-client-secret
OAUTH_REDIRECT_URI=http://localhost:8000/oauth/callback
OAUTH_AUTHORIZE_URL=https://provider.com/oauth/authorize
OAUTH_TOKEN_URL=https://provider.com/oauth/token
OAUTH_USER_URL=https://provider.com/api/me
OAUTH_LICENSES_URL=https://provider.com/api/me/licenses
```

### AI Translation Setup
Configure AI provider (currently supports OpenAI):
```env
AI_ENABLED=true
AI_PROVIDER=openai
AI_API_KEY=your-openai-api-key
AI_MODEL=gpt-4
AI_TEMPERATURE=0.3
```

## Architecture

### Key Components

#### Authorization Facade
Custom `Authorize` facade for permission checking:
```php
use App\Support\Facades\Authorize;

// Check any permission
Authorize::any(['edit-settings', 'manage-users']);

// Check all permissions
Authorize::all(['edit-settings', 'manage-users']);
```

#### Translation System
- Messages are automatically translated to all active locales
- Translations stored as separate message rows
- Original message linked via `original_ref` field
- Users see messages in their thread's locale

#### Admin Bootstrap
- First authenticated user automatically becomes admin
- Admins have all permissions by default
- Cannot accidentally remove admin status

## Development

### Code Style
Uses Laravel Pint for code formatting:
```bash
./vendor/bin/pint
```

### Testing
Run tests with:
```bash
php artisan test
```

### Frontend Development
For hot module replacement during development:
```bash
npm run dev
```

## Security

- All routes protected by authentication middleware
- Authorization checks at route and controller level
- Gate definitions for model-level authorization
- CSRF protection enabled
- SQL injection prevention via Eloquent ORM
- XSS protection in Vue templates

## License

This project is proprietary software.

## Support

For support, please create a thread in the system or contact the development team.
