# GroceryERP - Complete Grocery Store Management System

> A comprehensive Point of Sale (POS) and Enterprise Resource Planning (ERP) system built specifically for grocery stores and retail businesses.

[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-purple.svg)](https://laravel-livewire.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

---

## 📋 Table of Contents

- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Key Features Deep Dive](#key-features-deep-dive)
- [User Roles](#user-roles)
- [Documentation](#documentation)
- [Support](#support)
- [License](#license)

---

## ✨ Features

### 🛒 Point of Sale (POS)
- **Fast & Intuitive Interface** - Optimized for quick checkout
- **Barcode Scanning** - Support for product and package barcodes
- **Multiple Payment Methods** - Cash, bank transfer, credit sales
- **Customer Management** - Walk-in customers and registered customers
- **Loyalty Points** - Automatic points calculation and redemption
- **Price Adjustment** - Adjust prices between min/max range during sale
- **Box/Package Sales** - Sell by piece or by box with auto discounts
- **Batch Selection** - FIFO auto-selection or manual batch selection
- **Hold Bills** - Save multiple bills for later processing
- **Shift Management** - Track cashier shifts with opening/closing balances
- **Print Receipts** - Professional receipt printing with logo support
- **Promotions & Offers** - Automatic offer application

### 📦 Inventory Management
- **FIFO Batch Tracking** - First-In-First-Out inventory management
- **Batch-Specific Pricing** - Different min/max prices per batch
- **Supplier Tracking** - Know which supplier provided each batch
- **Stock Movements** - Complete audit trail of all stock changes
- **Expiry Management** - Track expiry dates and get alerts
- **Damaged Stock** - Separate tracking for damaged inventory
- **Stock Adjustments** - Manual corrections with reasons
- **Low Stock Alerts** - Automatic reorder level notifications
- **Multi-Unit Support** - Sell by piece, box, carton, etc.

### 🏪 Purchasing (GRN)
- **Goods Received Notes** - Complete purchase order management
- **Supplier Management** - Track suppliers, terms, outstanding balances
- **Batch Information** - Record batch numbers, expiry, manufacturing dates
- **Custom Pricing per Batch** - Set different min/max prices for each batch
- **Payment Tracking** - Track partial and full payments
- **GRN Approval Workflow** - Draft → Approved (updates stock)
- **Supplier Analytics** - Purchase history, payment reliability

### 👥 Customer Management
- **Customer Database** - Complete customer information
- **Loyalty Program** - Points-based rewards system
- **Purchase History** - Track all customer transactions
- **Credit Sales** - Allow credit purchases with tracking
- **Customer Analytics** - Lifetime value, frequency, top products

### 💰 Accounting & Finance
- **Double-Entry Accounting** - Complete journal entry system
- **Chart of Accounts** - Customizable account structure
- **Financial Reports** - P&L, Balance Sheet, Trial Balance
- **Payment Allocation** - Track payments against invoices
- **Outstanding Reports** - Customer credits and supplier payables
- **Bank Reconciliation** - Match bank transactions

### 📊 Reports & Analytics
- **Sales Reports** - Daily, weekly, monthly, custom date ranges
- **Inventory Reports** - Stock valuation, movement reports
- **Supplier Reports** - Purchase analysis, payment history
- **Customer Reports** - Sales analysis, loyalty points
- **Financial Reports** - Income statement, balance sheet
- **Export Options** - PDF and Excel export for all reports

### 🔐 Security & Audit
- **Role-Based Access Control** - Admin, Manager, Cashier roles
- **Activity Logging** - Complete audit trail of all actions
- **User Management** - Create and manage user accounts
- **Shift Controls** - Enforce shift open/close workflow
- **Data Validation** - Prevent invalid transactions

---

## 💻 System Requirements

- **PHP** >= 8.2
- **MySQL** >= 8.0 or **MariaDB** >= 10.3
- **Composer** >= 2.0
- **Node.js** >= 18.x (for asset compilation)
- **Web Server** - Apache or Nginx
- **Extensions:**
  - BCMath PHP Extension
  - Ctype PHP Extension
  - JSON PHP Extension
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
  - GD PHP Extension (for barcode generation)

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/GroceryErp.git
cd GroceryErp
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grocery_erp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations

```bash
# Run all migrations
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed
```

### 6. Compile Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### 7. Create Storage Symlink

```bash
php artisan storage:link
```

### 8. Set Permissions

```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Or use your web server user
```

### 9. Start the Application

```bash
# Development server
php artisan serve

# Visit: http://localhost:8000
```

### 10. Default Login Credentials

After seeding, use these credentials:

- **Admin:** admin@grocery.com / password
- **Manager:** manager@grocery.com / password
- **Cashier:** cashier@grocery.com / password

**⚠️ IMPORTANT:** Change these passwords immediately in production!

---

## ⚙️ Configuration

### Application Settings

Configure your store settings via **Settings** menu:

- Shop Name & Logo
- Address & Contact Information
- Currency Symbol
- Tax Settings
- Receipt Footer Text
- Loyalty Points Configuration

### Logo Setup

Place your logo at `/public/images/logo.png` or update the path in:
- Print receipt template: `resources/views/pos/print-receipt.blade.php`

Maximum recommended size: 120x80 pixels

### Barcode Configuration

The system supports:
- **EAN-13** - Standard 13-digit barcodes
- **Auto-generation** - Automatic barcode creation for products
- **Scanner Integration** - Works with any USB barcode scanner

---

## 🎯 Key Features Deep Dive

### FIFO Batch Inventory System

GroceryERP uses a sophisticated FIFO (First-In-First-Out) batch tracking system:

**How It Works:**
1. Each stock received (GRN) creates a **batch** (stock_movement record)
2. Each batch has its own:
   - Batch number
   - Supplier information (denormalized for speed)
   - Cost price
   - Min/Max selling prices
   - Expiry and manufacturing dates
3. When selling, the system automatically selects the **oldest batch** (FIFO)
4. All stock OUT movements are linked to their source batch for complete traceability

**Performance Optimization:**
- Supplier data is **denormalized** in stock_movements table
- Zero joins needed to display supplier names in POS
- Eliminates 100+ database joins on a typical POS screen

**Example:**
```
Batch 1: Received Jan 1, 100 units @ Rs. 50
Batch 2: Received Jan 5, 200 units @ Rs. 52

Sale on Jan 10 for 150 units:
- First 100 from Batch 1 (Rs. 50)
- Next 50 from Batch 2 (Rs. 52)
```

### Dynamic Pricing

Each batch can have different min/max selling prices:

```
Batch A (Premium Supplier): Min Rs. 60, Max Rs. 75
Batch B (Regular Supplier): Min Rs. 55, Max Rs. 70

Cashiers can adjust price between min/max during sale.
System prevents selling below minimum or above maximum.
```

### Loyalty Points System

Automatic points calculation and redemption:

- **Earn Points:** Configurable ratio (e.g., 1 point per Rs. 100)
- **Redeem Points:** Convert points to discount (e.g., 1 point = Rs. 1)
- **Point Transactions:** Complete audit trail
- **Automatic Application:** Points earned on each sale

### Double-Entry Accounting

All financial transactions automatically create journal entries:

**Sale Transaction:**
```
Dr: Cash/Bank Account
Cr: Sales Revenue

Dr: Cost of Goods Sold
Cr: Inventory
```

**Purchase Transaction:**
```
Dr: Inventory
Cr: Accounts Payable
```

Complete integration with financial reports!

---

## 👤 User Roles

### Admin
- **Full System Access**
- User management
- Settings configuration
- Financial reports
- System backup & restore

### Manager
- **Operational Management**
- Approve GRNs
- View all reports
- Manage inventory
- Up to 20% discount authority
- Handle returns and adjustments

### Cashier
- **POS Operations**
- Process sales
- Manage shifts
- Handle customer transactions
- Up to 5% discount authority
- Cannot approve GRNs or view financial reports

---

## 📚 Documentation

Comprehensive documentation available:

### For Users
- **README.md** (this file) - Overview and installation
- User guides available in `/docs` folder

### For Developers
- **[DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md)** - Complete technical reference
  - Database structure
  - Core models
  - Service layer
  - Common tasks
  - Best practices
  - Troubleshooting

- **[SERVICES.md](SERVICES.md)** - Detailed service documentation
  - InventoryService
  - POSService
  - TransactionService
  - And more...

---

## 🏗️ Architecture

### Technology Stack

**Backend:**
- Laravel 11 (PHP Framework)
- MySQL 8.0 (Database)
- Livewire 3 (Dynamic UI)

**Frontend:**
- Tailwind CSS (Styling)
- Alpine.js (Interactions)
- Blade Templates (Views)

**Key Patterns:**
- Service Layer Pattern
- Repository Pattern
- Observer Pattern (for events)
- FIFO Inventory Management
- Double-Entry Accounting

### Database Design Highlights

- **26 Tables** - Comprehensive data model
- **Denormalized Supplier Data** - Performance optimization
- **Batch Tracking** - Each stock IN = batch
- **Polymorphic Relations** - Flexible reference system
- **Complete Audit Trail** - Activity logging on all models

---

## 🔧 Maintenance

### Backup

```bash
# Database backup
php artisan backup:run

# Or use built-in backup feature in Settings menu
```

### Updates

```bash
# Update dependencies
composer update
npm update

# Run migrations
php artisan migrate

# Clear caches
php artisan optimize:clear
```

### Logs

Application logs are stored in:
- `storage/logs/laravel.log`
- Activity logs in database (`activity_log` table)

---

## 🐛 Troubleshooting

### Common Issues

**1. Stock Mismatch**
See DEVELOPER_GUIDE.md for stock verification procedures.

**2. Permission Denied**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

**3. Supplier Name Not Showing**
```bash
# Run backfill migration
php artisan migrate
```

**4. Session Errors**
```bash
# Clear application cache
php artisan optimize:clear
```

See **DEVELOPER_GUIDE.md** for detailed troubleshooting.

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 💡 Support

Need help? Here's how to get support:

### Documentation
- Check **DEVELOPER_GUIDE.md** for technical details
- Review **SERVICES.md** for service documentation

### Community
- GitHub Issues: Report bugs or request features
- Discussions: Ask questions and share ideas

---

## 🎯 Roadmap

### Upcoming Features

- [ ] Multi-store support
- [ ] Online ordering integration
- [ ] Mobile app (React Native)
- [ ] Advanced analytics dashboard
- [ ] Automated purchase orders
- [ ] Supplier portal
- [ ] Customer portal
- [ ] WhatsApp integration for receipts
- [ ] Advanced promotions engine
- [ ] Inventory forecasting

---

## 🙏 Acknowledgments

Built with:
- [Laravel](https://laravel.com) - The PHP Framework
- [Livewire](https://laravel-livewire.com) - Dynamic UI Components
- [Tailwind CSS](https://tailwindcss.com) - Utility-First CSS
- [Alpine.js](https://alpinejs.dev) - Lightweight JavaScript

Special thanks to all contributors and users!

---

<div align="center">

**Made with ❤️ for Grocery Stores Worldwide**

[⬆ Back to Top](#groceryerp---complete-grocery-store-management-system)

</div>
