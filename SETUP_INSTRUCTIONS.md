# Product Management System Setup Instructions

## After pulling this branch, run the following commands:

1. Install dependencies:
```bash
composer install
npm install
```

2. Create storage link for product images:
```bash
php artisan storage:link
```

3. Run migrations:
```bash
php artisan migrate
```

4. Seed the database with sample products:
```bash
php artisan db:seed --class=ProductSeeder
```

## Features Included:

- Products migration with full schema (SKU, barcode, pricing, stock levels, packaging support)
- Product packaging migration for box/carton support
- Product and ProductPackaging models with relationships
- Livewire CRUD components (create, edit, list, delete)
- Product management UI with tabs (Basic Info, Pricing, Packaging, Image)
- Image upload support (stored in storage/app/public/products)
- Search and filter functionality (by name, SKU, barcode, category, status)
- Product seeder with 10 sample products
- Sidebar menu updated with Products link
- Role-based access (admin, manager, store_keeper)

## Notes:

- Stock levels (current_stock_quantity, damaged_stock_quantity) are read-only in the product form
- Stock will be managed via GRN (Goods Receipt Note) and POS transactions (to be implemented)
- Products with existing stock cannot be deleted
- Products can have optional packaging with discount support
- Barcode generation available for both products and packages
