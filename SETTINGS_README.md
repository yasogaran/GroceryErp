# Settings System Documentation

## Overview
The Settings System provides a flexible and user-friendly way to manage various configuration options for the Grocery Shop ERP application. Settings are stored in the database and can be managed through an intuitive web interface accessible to admin users.

## Features

### 1. Database Structure
- **Migration**: `2024_11_15_000002_create_settings_table.php`
- **Table**: `settings`
- **Fields**:
  - `id`: Primary key
  - `key`: Unique setting identifier (indexed)
  - `value`: Setting value (text)
  - `group_name`: Category/group of the setting (indexed)
  - `description`: Human-readable description
  - `timestamps`: Created and updated timestamps

### 2. Settings Model
- **Location**: `app/Models/Setting.php`
- **Features**:
  - Cached retrieval for improved performance
  - Static methods for getting and setting values
  - Automatic cache invalidation on updates
  - Grouped retrieval by category

### 3. Helper Function
- **Function**: `settings($key, $default = null)`
- **Location**: `app/helpers.php`
- **Usage**: Retrieve settings anywhere in the application
- **Example**:
  ```php
  $shopName = settings('shop_name', 'Default Shop');
  $currencySymbol = settings('currency_symbol', '$');
  ```

### 4. Settings Management Interface
- **Route**: `/admin/settings` (Admin only)
- **Component**: `App\Livewire\Settings\SettingsManagement`
- **View**: `resources/views/livewire/settings/settings-management.blade.php`

## Default Settings

### General Settings
- **shop_name**: My Grocery Shop
- **shop_address**: 123 Main Street
- **shop_phone**: 0123456789
- **shop_email**: info@grocery.local
- **shop_logo**: (empty - can be uploaded via interface)

### Inventory Settings
- **low_stock_threshold**: 10
- **expiry_alert_days**: 30

### POS Settings
- **pos_tax_rate**: 0
- **pos_allow_discount**: 1 (yes)

### Receipt Settings
- **receipt_header**: Thank you for shopping with us!
- **receipt_footer**: Please come again!

### Accounting Settings
- **currency_symbol**: Rs.
- **date_format**: d-m-Y

## Installation Steps

### 1. Install Dependencies
```bash
composer install
composer dump-autoload
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database
Edit `.env` file and set your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grocery_erp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Seed Default Settings
```bash
php artisan db:seed --class=SettingsSeeder
```

Or seed all:
```bash
php artisan db:seed
```

### 6. Create Storage Link (for logo uploads)
```bash
php artisan storage:link
```

## Usage Examples

### Retrieving Settings in Controllers
```php
use App\Models\Setting;

public function index()
{
    $shopName = Setting::get('shop_name', 'Default Name');
    // or using helper
    $shopName = settings('shop_name', 'Default Name');
}
```

### Using in Blade Templates
```blade
<h1>{{ settings('shop_name') }}</h1>
<p>{{ settings('shop_address') }}</p>
<img src="{{ asset('storage/' . settings('shop_logo')) }}" alt="Logo">
```

### Updating Settings Programmatically
```php
use App\Models\Setting;

Setting::set('shop_name', 'New Shop Name', 'general', 'Name of the grocery shop');
```

### Getting All Settings by Group
```php
$generalSettings = Setting::where('group_name', 'general')->get();
// or
$allGrouped = Setting::getAllGrouped();
```

## Settings Management Interface

### Access
- **URL**: `/admin/settings`
- **Permission**: Admin role only
- **Menu**: Available in the sidebar under "Settings"

### Features
1. **Tabbed Interface**: Settings organized by category
   - General
   - Inventory
   - POS
   - Receipt
   - Accounting

2. **Logo Upload**:
   - Supports image files (JPG, PNG, etc.)
   - Maximum file size: 2MB
   - Preview before saving
   - Remove logo functionality
   - Automatic old logo cleanup

3. **Form Validation**:
   - Required fields marked with *
   - Email validation for shop_email
   - Numeric validation for thresholds and rates
   - Real-time error messages

4. **Success/Error Notifications**:
   - Flash messages on save
   - Validation error display
   - Confirmation on successful updates

## API

### Setting Model Methods

#### `Setting::get($key, $default = null)`
Retrieve a setting value with optional default.

**Parameters**:
- `$key` (string): Setting key
- `$default` (mixed): Default value if setting not found

**Returns**: Mixed value

**Example**:
```php
$threshold = Setting::get('low_stock_threshold', 10);
```

#### `Setting::set($key, $value, $groupName = 'general', $description = null)`
Create or update a setting.

**Parameters**:
- `$key` (string): Setting key
- `$value` (mixed): Setting value
- `$groupName` (string): Group name (default: 'general')
- `$description` (string|null): Setting description

**Returns**: Setting instance

**Example**:
```php
Setting::set('shop_name', 'My New Shop', 'general', 'Shop name');
```

#### `Setting::getAllGrouped()`
Get all settings grouped by group_name.

**Returns**: Collection of settings grouped by group_name

**Example**:
```php
$grouped = Setting::getAllGrouped();
foreach ($grouped as $group => $settings) {
    echo "Group: $group\n";
    foreach ($settings as $setting) {
        echo "  {$setting->key}: {$setting->value}\n";
    }
}
```

#### `Setting::clearCache()`
Clear all settings cache.

**Example**:
```php
Setting::clearCache();
```

## Caching

Settings are cached for **1 hour (3600 seconds)** to improve performance. The cache is automatically cleared when:
- A setting is updated via `Setting::set()`
- A setting is saved through the management interface
- `Setting::clearCache()` is called manually

Cache keys follow the pattern: `setting_{key}`

## Security

- Access restricted to admin role only via `check.role:admin` middleware
- File upload validation (type and size)
- CSRF protection on all forms
- XSS protection via Laravel's default escaping

## Extending the Settings System

### Adding New Settings

1. **Via Seeder**: Add to `SettingsSeeder.php`
```php
[
    'key' => 'new_setting',
    'value' => 'default_value',
    'group_name' => 'general',
    'description' => 'Description of new setting',
]
```

2. **Via Interface**: Use the settings management page

3. **Programmatically**:
```php
Setting::set('new_setting', 'value', 'group_name', 'description');
```

### Adding New Setting Groups

1. Update `SettingsSeeder` with new group settings
2. Add new tab in `settings-management.blade.php`:
```blade
<button type="button" wire:click="setActiveTab('new_group')"...>
    New Group
</button>
```

3. Add corresponding form fields in the tab content section

## Troubleshooting

### Settings Not Saving
- Check file permissions on `storage/` directory
- Verify database connection
- Check Laravel logs: `storage/logs/laravel.log`

### Logo Upload Failing
- Ensure storage link exists: `php artisan storage:link`
- Check `storage/app/public/logos` directory permissions
- Verify `public/storage` symlink exists

### Cache Issues
- Clear application cache: `php artisan cache:clear`
- Clear settings cache programmatically: `Setting::clearCache()`

## File Structure
```
app/
├── Livewire/
│   └── Settings/
│       └── SettingsManagement.php
├── Models/
│   └── Setting.php
└── helpers.php

database/
├── migrations/
│   └── 2024_11_15_000002_create_settings_table.php
└── seeders/
    ├── SettingsSeeder.php
    └── DatabaseSeeder.php

resources/
└── views/
    └── livewire/
        └── settings/
            └── settings-management.blade.php

routes/
└── web.php
```

## Notes

- Always use the helper function `settings()` for retrieving settings in application code
- Settings are cached for performance - use `Setting::clearCache()` if manual cache clearing is needed
- Logo files are stored in `storage/app/public/logos`
- Date format follows PHP's date() function format
- Currency symbol is a string and can be any character(s)
