# Application Layout and Navigation

This document describes the main application layout and navigation system for the Grocery Shop ERP.

## Features Implemented

### 1. Main Application Layout (`resources/views/layouts/app.blade.php`)
- **Sidebar Navigation**: Fixed left sidebar (~240px width) with dark theme
- **Top Bar**: Sticky header with user info, role badge, and logout functionality
- **Main Content Area**: Responsive content area with proper spacing
- **Mobile Responsive**: Hamburger menu toggle for mobile devices using Alpine.js
- **Overlay**: Click-outside to close sidebar on mobile

### 2. Sidebar Navigation (`resources/views/layouts/sidebar-navigation.blade.php`)
Role-based menu items with automatic visibility control:

| Menu Item | Accessible Roles |
|-----------|------------------|
| Dashboard | All authenticated users |
| POS | cashier, manager, admin |
| Inventory | store_keeper, manager, admin |
| Purchase | manager, admin |
| Sales | manager, admin |
| Customers | cashier, manager, admin |
| Reports | manager, admin |
| Accounting | accountant, manager, admin |
| Settings | admin |
| User Management | admin |

**Features**:
- Active menu highlighting (blue left border and background)
- SVG icons for each menu item
- Automatic route existence checking
- Responsive design

### 3. Dashboard Component (`app/Livewire/Dashboard.php`)
- Simple dashboard with welcome message
- Placeholder stat cards for:
  - Total Sales
  - Inventory Items
  - Pending Orders
  - Customers
- User role display
- Ready for future widget integration

### 4. Authentication System

#### Login Component (`app/Livewire/Auth/Login.php`)
- Email/password authentication
- Remember me functionality
- Active user check (inactive users cannot log in)
- Loading states with spinner
- Error handling and validation

#### Guest Layout (`resources/views/layouts/guest.blade.php`)
- Clean, centered login form
- Responsive design
- Consistent branding

### 5. Routes (`routes/web.php`)
```php
// Guest routes
GET  /login         - Login page

// Auth action routes
POST /logout        - Logout (session invalidation)

// Authenticated routes
GET  /dashboard     - Main dashboard (all users)
GET  /admin/users   - User management (admin only)
```

### 6. Test Users (Database Seeder)
The following test users are available:

| Email | Password | Role | Access |
|-------|----------|------|--------|
| admin@example.com | password | admin | Full system access |
| manager@example.com | password | manager | Management features |
| cashier@example.com | password | cashier | POS, Customers, Dashboard |
| keeper@example.com | password | store_keeper | Inventory, Dashboard |
| accountant@example.com | password | accountant | Accounting, Dashboard |

## Installation & Setup

1. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

2. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=UserSeeder
   ```

4. **Build Assets**:
   ```bash
   npm run dev
   ```

5. **Start Server**:
   ```bash
   php artisan serve
   ```

6. **Login**:
   - Navigate to `http://localhost:8000/login`
   - Use any test user credentials above
   - You'll be redirected to the dashboard

## Technical Details

### Technology Stack
- **Backend**: Laravel 11.x with Livewire 3
- **Frontend**: Tailwind CSS 3.x
- **JavaScript**: Alpine.js (for sidebar toggle and dropdowns)
- **Authentication**: Laravel's built-in auth system

### Key Features
- **Role-Based Access Control**: Middleware `check.role` restricts access based on user roles
- **Active Menu Highlighting**: Automatically highlights current route in sidebar
- **Mobile Responsive**: Full mobile support with slide-out sidebar
- **Session Management**: Secure logout with session invalidation
- **User Avatars**: Auto-generated initials in colored circles

### File Structure
```
app/
├── Livewire/
│   ├── Auth/
│   │   └── Login.php
│   ├── Dashboard.php
│   └── Users/
│       ├── UserManagement.php
│       ├── CreateUser.php
│       └── EditUser.php
├── Http/Middleware/
│   └── CheckRole.php

resources/views/
├── layouts/
│   ├── app.blade.php              # Main authenticated layout
│   ├── guest.blade.php            # Guest/login layout
│   └── sidebar-navigation.blade.php
└── livewire/
    ├── auth/
    │   └── login.blade.php
    ├── dashboard.blade.php
    └── users/
        ├── user-management.blade.php
        ├── create-user.blade.php
        └── edit-user.blade.php

database/
├── migrations/
│   └── 2024_11_15_000001_add_role_and_audit_fields_to_users_table.php
└── seeders/
    └── UserSeeder.php
```

## Future Enhancements
The following features are planned for future iterations:
- Real-time dashboard statistics
- Charts and graphs
- Module-specific pages (POS, Inventory, Sales, etc.)
- User profile management
- Password reset functionality
- Two-factor authentication
- Activity logs
- Notifications system

## Notes
- All menu items (except Dashboard and User Management) are placeholders
- Routes for POS, Inventory, Purchase, Sales, Customers, Reports, Accounting, and Settings are commented out
- These will be implemented in future tasks
- The sidebar automatically hides menu items based on user role
- Mobile sidebar closes when clicking outside (Alpine.js click.away)
