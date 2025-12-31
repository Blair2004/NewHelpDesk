# Ticketing System – Full Instruction Document (Agent Mode)
## Version 1.0 – Final (Current)

---

## 0. Purpose

Build a **standalone, production-grade ticketing system** using **Laravel 12 + Vue 3**.

The system integrates with an external platform **ONLY** for:
- OAuth authentication
- User identity & locale
- Roles/permissions (optional, read-only)
- Licenses/purchases (read-only)

Everything else is owned locally.

This application acts as:
- a public support forum
- a private ticketing/helpdesk
- a multilingual, AI-assisted system

---

## 1. Core Principles

- Standalone & decoupled
- OAuth for authentication only
- Platform integration is **read-only**
- Strong separation of concerns
- Async-first (queues)
- No swallowed exceptions
- Explicit authorization everywhere
- Tested features only
- Clean, modern code style

---

## 2. Technology Stack

### Backend
- Laravel 12
- OAuth 2.0 client
- Queue system (DB or Redis)
- Laravel Gate & Policies
- REST API (JSON only)

### Frontend
- Vue 3 (Composition API)
- Vite
- TailwindCSS (latest)
- Pinia
- Moment.js

---

## 3. Platform Integration (STRICT)

### Platform is used ONLY for:
- OAuth authentication
- User identity (`external_user_id`)
- Preferred locale
- Roles / permissions (if provided)
- Licenses / purchases

### Platform is NEVER used for:
- Threads
- Messages
- Translations
- Status
- Assignments
- Settings
- Reports

### Required Platform Endpoints
- OAuth authorize / token
- `GET /api/me`
- `GET /api/me/licenses`

---

## 4. Data Ownership

### Platform (External)
- user identity
- name, email, avatar
- preferred locale
- roles / permissions
- licenses

### Ticketing System (Local DB)
- threads
- messages (original + translated rows)
- message revisions (staff-only)
- categories
- tags
- departments
- assignments
- attachments
- settings
- user preferences
- reports

---

## 5. Authentication Flow

1. User authenticates via OAuth
2. Platform returns identity payload
3. Local shadow user is created/updated
4. Platform locale used as default
5. Local preferences override platform defaults inside this app

---

## 6. User Profile & Preferences

### Editable (Local)
- preferred language (locale)
- timezone (optional)
- date format (optional)

### Read-only (Platform)
- name
- email
- avatar
- licenses

### Rules
- Preferred language defines:
  - UI language (if enabled)
  - thread locale on creation
- Users **cannot change thread locale after creation**

---

## 7. Localization System

- Known list of languages supported by AI model
- Admin activates languages
- One default locale is required
- Thread locale is immutable

---

## 8. Threads (Tickets)

### Thread Fields
- title
- category
- locale
- visibility: `public | private`
- status: `open | pending | resolved | closed`
- locked: `true | false`
- assigned user (nullable)
- assigned department (nullable)
- tags

### Visibility
- Public → visible to everyone
- Private → owner, participants, staff

---

## 9. Messages – Multilingual Model

### Strategy
**Translations are stored as regular message rows.**

### thread_messages fields
- id
- ticket_id
- author_id
- author_type (`user | agent | system`)
- content
- locale
- visibility (`public | sensitive`)
- original_ref (nullable)
- created_at / updated_at

### Rules
- Original message → `original_ref = null`
- Translation:
  - `original_ref = original_message.id`
  - `locale = target locale`
  - `author_type = system`
- One translation per locale per original message

---

## 10. Message Delivery Rules

### Customer View
- Only messages where `locale = threads.locale`
- Missing translations trigger async generation

### Staff View
- Can view:
  - original messages
  - localized messages
  - revision history

---

## 11. Sensitive Messages

- Hidden from guests & unrelated users
- Translations generated **only for authorized viewers**

---

## 12. Message Editing & Revisions

- Users can edit own messages
- Staff can edit messages (permission-based)
- Old versions preserved
- **Only staff can view revisions**

---

## 13. Attachments (Images)

Users can attach images via:
- drag & drop
- click upload
- clipboard paste

Rules:
- image-only
- validation enforced
- preview before send
- async upload recommended

---

## 14. Ticket Lifecycle

### Status
- open
- pending
- resolved
- closed

### Locking
- locked threads:
  - no replies
  - no edits
  - optional staff internal notes

Status and locking are independent.

---

## 15. Dashboard Layout

### Structure
- Left sidebar (navigation + filters)
- Center area (thread list / chat)
- Right panel (thread metadata)

### Pages
- Home
- Threads
- Departments
- Customers
- Reports
- Settings
- Profile
- Access Control (Roles & Permissions)

---

## 16. Home Dashboard

Widgets:
- new threads
- stale threads (configurable delay)
- weekly performance:
  - open
  - resolved
  - closed
  - locked

---

## 17. Threads Page

Filters:
- all conversations
- unassigned
- assigned to me
- by status
- by category

Thread view:
- chat-style UI
- right panel:
  - participants (past + present)
  - last user reply
  - category (editable)
  - tags
  - locale
  - status

---

## 18. Departments

- handling groups
- threads assigned to departments
- reassignment requires permission

---

## 19. Customers

- non-staff users only
- metrics:
  - threads opened
  - status (active / blocked)

---

## 20. Reports

Leaderboards:
- most resolved
- most closed
- most stale

Weekly charts:
- opened
- closed
- locked
- stale

---

## 21. Settings (Tabbed)

### General
- logos (light/dark, square/rectangle)
- currency
- timezone
- date format

### Localization
- active languages
- default locale

### AI Integration
- provider
- models
- API keys
- enable/disable

### Working Time
- weekly schedule
- timezone-aware

---

## 22. AI Translation Pipeline

- Queue-based only
- Triggered on message creation/edit
- Creates translated message rows
- Failures retry gracefully

---

## 23. Roles, Permissions & Authorization

### Admin Bootstrap Rule
- All users are regular users by default
- **First authenticated user becomes admin**
- Admins managed locally afterward

### RBAC Model
- roles
- permissions
- role_permission
- user_role

Admin role:
- implicitly has all permissions
- cannot be accidentally removed

---

## 24. Authorization Facade (FINAL)

### Facade Name
**Authorize**

### Methods

#### Any permission (OR)
```php
Authorize::any(['edit-settings', 'manage-users']);
```

#### All permissions (AND)
```php
Authorize::all(['edit-settings', 'manage-users']);
```

### Route Usage (Required)
```php
Route::middleware([
    'auth',
    Authorize::any(['edit-settings'])
])->get('/settings', ...);
```

### Admin Bypass
- Admin role always passes

---

## 25. Gate Usage (Mandatory)

Defense in depth is required.

```php
Gate::authorize('permission-name');
```

or

```php
$response = Gate::inspect('permission-name');
```

Routes, controllers, services, and jobs must enforce authorization.

---

## 26. Engineering Standards

### Architecture
- Thin controllers
- Actions / Use-cases
- Jobs for async tasks
- Policies for model authorization

### Async via Jobs
- email sending
- AI translation
- heavy processing

### Exceptions
- Never swallow
- Never log-and-continue silently

---

## 27. Testing (Mandatory)

- Unit tests for business logic
- Feature tests for APIs
- Queue, Mail, HTTP fakes
- Tests required for every feature

---

## 28. Code Style & Formatting

### Laravel Pint (MANDATORY)

Use this configuration **as-is**.

### Imports Rule (STRICT)

❌ No fully-qualified class names inline  
✅ Always use `use` imports

---

## 29. Frontend Animations (Chat UX)

- Message added: subtle pop-in
- Message deleted: fade + collapse
- Use Vue `TransitionGroup`
- Respect `prefers-reduced-motion`

---

## 30. Non-Goals

- Live chat
- CRM
- Platform data mutation
- Real-time translation

---

## 31. Success Criteria

The system is complete when:
- OAuth works reliably
- Multilingual threads behave correctly
- Authorization is airtight
- Code is tested and formatted
- Platform remains read-only
- UX is modern and smooth

## Template Reference:
Dashboard layout inspiration: message-dashboard.png