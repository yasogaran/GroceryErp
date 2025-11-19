# Toast Notifications Guide

This application includes a global toast notification system that displays popup messages for user feedback across all pages.

## Overview

The toast notification system consists of:
- **Toast Livewire Component** (`app/Livewire/Toast.php` + `resources/views/livewire/toast.blade.php`)
- **WithToast Trait** (`app/Traits/WithToast.php`) - Helper methods for easy integration
- **Global Integration** - Automatically included in all pages via the app layout

## Features

- ✅ **4 Notification Types**: success, error, warning, info
- ✅ **Auto-dismiss**: Notifications automatically disappear after a set duration
- ✅ **Sound Effects**: Optional notification sound for better UX
- ✅ **Smooth Animations**: Slide-in and fade-out transitions
- ✅ **Session Flash Support**: Automatically picks up Laravel session flash messages
- ✅ **Multiple Notifications**: Shows up to 5 notifications simultaneously
- ✅ **Inline + Toast**: Works alongside inline validation errors

## Usage in Livewire Components

### Method 1: Using the WithToast Trait (Recommended)

1. **Add the trait to your component:**

```php
<?php

namespace App\Livewire\YourModule;

use App\Traits\WithToast;
use Livewire\Component;

class YourComponent extends Component
{
    use WithToast;

    // Your component code...
}
```

2. **Use the toast methods:**

```php
public function save()
{
    try {
        $this->validate();

        // Your save logic...

        // Show success notification
        $this->toastSuccess('Record saved successfully!');

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Show validation errors
        $this->toastValidationErrors($e);
        throw $e; // Re-throw to show inline errors

    } catch (\Exception $e) {
        // Show error notification
        $this->toastError('Failed to save: ' . $e->getMessage());
        throw $e;
    }
}

public function delete($id)
{
    // Show warning
    $this->toastWarning('This action cannot be undone!');
}

public function info()
{
    // Show info
    $this->toastInfo('This is an informational message');
}
```

### Method 2: Direct Event Dispatch

If you don't want to use the trait, dispatch events directly:

```php
// Success notification
$this->dispatch('showToast', type: 'success', message: 'Operation completed!');

// Error notification (longer duration)
$this->dispatch('showToast', type: 'error', message: 'Something went wrong!', duration: 7000);

// Warning notification
$this->dispatch('showToast', type: 'warning', message: 'Please review your input');

// Info notification
$this->dispatch('showToast', type: 'info', message: 'Did you know?');
```

### Method 3: Session Flash (for redirects)

When redirecting from controllers or after form submissions:

```php
// In controllers or Livewire redirects
session()->flash('success', 'User created successfully!');
session()->flash('error', 'Failed to delete record');
session()->flash('warning', 'Your trial period is ending');
session()->flash('info', 'New features available!');

return redirect()->route('users.index');
```

The Toast component automatically picks up these session flashes and displays them.

## Available Methods (WithToast Trait)

### `toastSuccess($message, $duration = 5000)`
Display a green success notification.

```php
$this->toastSuccess('Product created successfully!');
$this->toastSuccess('Data exported!', 3000); // Custom duration
```

### `toastError($message, $duration = 7000)`
Display a red error notification (longer default duration).

```php
$this->toastError('Failed to connect to database');
$this->toastError('Invalid credentials', 5000);
```

### `toastWarning($message, $duration = 6000)`
Display a yellow warning notification.

```php
$this->toastWarning('Low stock alert!');
$this->toastWarning('Unsaved changes will be lost');
```

### `toastInfo($message, $duration = 5000)`
Display a blue informational notification.

```php
$this->toastInfo('Import process started in background');
$this->toastInfo('Keyboard shortcut: Ctrl+S to save');
```

### `toastValidationErrors($validationException)`
Automatically formats and displays validation errors from Laravel's ValidationException.

```php
try {
    $this->validate();
} catch (\Illuminate\Validation\ValidationException $e) {
    $this->toastValidationErrors($e);
    throw $e; // Keep inline errors
}
```

## Duration Guidelines

- **Success**: 5 seconds (default)
- **Info**: 5 seconds (default)
- **Warning**: 6 seconds (default)
- **Error**: 7 seconds (default) - longer for users to read error details

You can customize duration for any toast by passing the `$duration` parameter in milliseconds.

## Best Practices

1. **Use with Inline Errors**: Toast notifications complement (not replace) inline validation errors
   ```php
   catch (\Illuminate\Validation\ValidationException $e) {
       $this->toastValidationErrors($e);
       throw $e; // Keep inline errors visible
   }
   ```

2. **Keep Messages Concise**: Aim for 1-2 short sentences
   ```php
   // Good
   $this->toastSuccess('Product created!');

   // Too long (consider using a modal instead)
   $this->toastInfo('Your product has been successfully created and is now visible in the catalog. You can edit it anytime from the product management page.');
   ```

3. **Use Appropriate Types**:
   - ✅ **Success**: Completed actions (save, delete, update)
   - ❌ **Error**: Failed operations, exceptions, validation errors
   - ⚠️ **Warning**: Cautions, confirmations needed, potential issues
   - ℹ️ **Info**: Tips, background processes, general information

4. **Don't Overuse**: Only show toasts for important user feedback
   ```php
   // Good - important feedback
   $this->toastSuccess('Payment processed!');

   // Bad - unnecessary toast
   $this->toastInfo('Button clicked'); // No toast needed
   ```

## Examples from the Codebase

### Product Creation (CreateProduct.php)
```php
public function save()
{
    try {
        $validated = $this->validate();

        // Create product logic...

        $this->toastSuccess('Product created successfully!');
        $this->dispatch('product-created');

    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->toastValidationErrors($e);
        throw $e;
    } catch (\Exception $e) {
        $this->toastError('Failed to create product: ' . $e->getMessage());
        throw $e;
    }
}
```

### Product Update (EditProduct.php)
```php
public function update()
{
    try {
        $validated = $this->validate();

        // Update product logic...

        $this->toastSuccess('Product updated successfully!');
        $this->dispatch('product-updated');

    } catch (\Illuminate\Validation\ValidationException $e) {
        $this->toastValidationErrors($e);
        throw $e;
    } catch (\Exception $e) {
        $this->toastError('Failed to update product: ' . $e->getMessage());
        throw $e;
    }
}
```

## Customization

### Disable Sound Effects

The sound can be disabled by setting `enableSound` to false in the Toast component:

```php
// In app/Livewire/Toast.php
public $enableSound = false;
```

### Change Toast Position

Edit the container class in `resources/views/livewire/toast.blade.php`:

```html
<!-- Current: top-right -->
class="fixed top-4 right-4 z-50..."

<!-- Bottom-right -->
class="fixed bottom-4 right-4 z-50..."

<!-- Top-center -->
class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50..."
```

### Customize Colors

Toast colors are defined using Tailwind CSS classes in the view template. Edit `resources/views/livewire/toast.blade.php` to change colors.

## Troubleshooting

### Toasts not appearing?

1. Verify the Toast component is included in your layout:
   ```blade
   <!-- In resources/views/components/layouts/app.blade.php -->
   @livewire('toast')
   ```

2. Check browser console for JavaScript errors

3. Ensure Alpine.js is loaded (required for animations)

### Multiple toasts showing at once?

This is normal behavior (up to 5). Older toasts will automatically be removed to prevent cluttering the screen.

### Session flash messages not showing?

The Toast component automatically picks up session flashes in its `mount()` method. Make sure you're using standard Laravel flash keys: `success`, `error`, `warning`, `info`.

## Future Enhancements

Potential improvements:
- [ ] Configurable max toasts limit
- [ ] Persistent toasts (require manual dismiss)
- [ ] Toast with action buttons
- [ ] Different sound effects per type
- [ ] Dark mode support
- [ ] Mobile-optimized positioning

## Support

For questions or issues with toast notifications, check:
- Toast component: `app/Livewire/Toast.php`
- Toast view: `resources/views/livewire/toast.blade.php`
- WithToast trait: `app/Traits/WithToast.php`
- App layout: `resources/views/components/layouts/app.blade.php`
