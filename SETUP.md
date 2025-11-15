# Grocery ERP - User Management Setup Guide

This document provides instructions for setting up the multi-role user management system in the Grocery Shop ERP.

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL or PostgreSQL database

## Installation Steps

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer update

# Install Node dependencies
npm install
```

### 2. Environment Configuration

Copy `.env.example` to `.env` (if not already done):

```bash
cp .env.example .env
```

Configure your database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grocery_erp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Generate application key:

```bash
php artisan key:generate
```

### 3. Run Migrations and Seeders

```bash
# Run migrations to create tables
php artisan migrate

# Seed the database with the initial admin user
php artisan db:seed --class=UserSeeder
```

### 4. Build Assets

```bash
# Build frontend assets with Vite
npm run build

# Or run in development mode
npm run dev
```

### 5. Start the Application

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Default Admin Credentials

After running the seeder, you can log in with:

- **Email:** admin@grocery.local
- **Password:** password

**Important:** Change the default password after first login!

## User Roles

The system supports the following roles:

- **Admin:** Full access to all features including user management
- **Manager:** Store management capabilities
- **Cashier:** Point of sale operations
- **Store Keeper:** Inventory management
- **Accountant:** Financial operations

## Features

### User Management (Admin Only)

Access user management at: `/admin/users`

Features include:

1. **User List**
   - Search users by name or email
   - Filter by role
   - Pagination
   - View audit information (created by, updated by)

2. **Create User**
   - Set name, email, password
   - Assign role
   - Set active status
   - Automatic audit trail

3. **Edit User**
   - Update user information
   - Change role
   - Change password (optional)
   - Toggle active status

4. **Deactivate User**
   - Toggle user active status
   - Deactivated users cannot log in
   - Cannot deactivate your own account

5. **Delete User**
   - Remove users from the system
   - Cannot delete users with audit trail references
   - Cannot delete your own account

## Middleware

### CheckRole Middleware

Protects routes based on user roles:

```php
Route::middleware(['auth', 'check.role:admin'])->group(function () {
    // Admin only routes
});

Route::middleware(['auth', 'check.role:admin,manager'])->group(function () {
    // Admin and Manager routes
});
```

The middleware also checks if the user is active and logs them out if deactivated.

## Database Schema

### Users Table Enhancements

- `role`: ENUM('admin', 'manager', 'cashier', 'store_keeper', 'accountant')
- `is_active`: BOOLEAN (default: true)
- `created_by`: Foreign key to users table
- `updated_by`: Foreign key to users table

## File Structure

```
app/
├── Http/
│   └── Middleware/
│       └── CheckRole.php
├── Livewire/
│   └── Users/
│       ├── UserManagement.php
│       ├── CreateUser.php
│       └── EditUser.php
└── Models/
    └── User.php

database/
├── migrations/
│   └── 2024_11_15_000001_add_role_and_audit_fields_to_users_table.php
└── seeders/
    ├── DatabaseSeeder.php
    └── UserSeeder.php

resources/
└── views/
    ├── layouts/
    │   └── app.blade.php
    └── livewire/
        └── users/
            ├── user-management.blade.php
            ├── create-user.blade.php
            └── edit-user.blade.php

routes/
└── web.php
```

## Troubleshooting

### Issue: Livewire components not loading

Make sure you have run:
```bash
composer require livewire/livewire
php artisan livewire:publish --config
```

### Issue: Styles not applied

Make sure you have built the assets:
```bash
npm install
npm run build
```

### Issue: 403 Forbidden when accessing /admin/users

- Make sure you're logged in
- Make sure your user has the 'admin' role
- Check that the middleware is registered in `bootstrap/app.php`

## Security Considerations

1. Always change default passwords
2. Use strong password policies (configured in User model)
3. Regularly audit user access logs
4. Deactivate users instead of deleting when possible (maintains audit trail)
5. Review and update user roles periodically

## Next Steps

- Set up Laravel Breeze for authentication (if not already installed)
- Configure email verification
- Add password reset functionality
- Implement activity logging
- Add role-based dashboard views
- Create additional modules (products, sales, inventory, etc.)

## Support

For issues or questions, please refer to the Laravel and Livewire documentation:
- Laravel: https://laravel.com/docs
- Livewire: https://livewire.laravel.com/docs
