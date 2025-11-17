#!/bin/bash

# Clear all Laravel caches
echo "Clearing Laravel caches..."

# Clear route cache
php artisan route:clear
echo "✓ Route cache cleared"

# Clear config cache
php artisan config:clear
echo "✓ Config cache cleared"

# Clear view cache
php artisan view:clear
echo "✓ View cache cleared"

# Clear application cache
php artisan cache:clear
echo "✓ Application cache cleared"

# Recreate the caches (optional, for production)
# php artisan route:cache
# php artisan config:cache
# php artisan view:cache

echo ""
echo "All caches cleared! Try accessing the financial reports now."
echo ""
echo "Financial report URLs:"
echo "  - /reports/financial/trial-balance"
echo "  - /reports/financial/profit-and-loss"
echo "  - /reports/financial/balance-sheet"
echo "  - /reports/financial/ledger"
echo "  - /reports/financial/day-book"
