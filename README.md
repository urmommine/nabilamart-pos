# Nabila Mart POS 🚀

A modern, high-performance Point of Sale (POS) and Inventory Management System built specifically for retail stores and UMKM (Micro, Small, and Medium Enterprises). 

Built with **Laravel 12** and **Filament 3.3**, this system provides a seamless experience for managing sales, inventory, and reporting in real-time.

---

## ✨ Key Features

### 🏢 Inventory Management
- **Smart Tracking**: Real-time stock level monitoring with SKU and barcode integration.
- **Stock Alerts**: Automatic notifications and dashboard widgets for low-stock items.
- **Categorization**: Organize products efficiently with a flexible category system.
- **Cost Tracking**: Monitor purchase vs. selling prices to calculate profit margins automatically.

### 💰 Next-Gen Point of Sale (POS)
- **High-Speed Checkout**: Livewire-powered POS terminal for a desktop-app feel.
- **Barcode Ready**: Immediate item lookup and addition via barcode scanning (support for external HID scanners).
- **Flexible Discounts**: 
    - **Member Discounts**: Automatically apply percentage/fixed discounts for registered customers.
    - **Special Pricing**: Product-specific discounts per customer.
    - **Manual Overrides**: Apply custom discounts to individual items or the entire transaction during checkout.
- **Tax Management**: Dynamic tax calculation (Fixed or Percentage) with simple toggle in the POS.
- **Multi-Payment Support**: Accept Cash, Bank Transfer, or Integrated E-wallet payments.
- **Receipt Printing**: 
    - **Multi-Mode Printing**: Supports both Direct Server-Side printing (ESC/POS) and Client-Side printing via **PrintHub.js**.
    - Native support for ESC/POS thermal printers (USB/Network/Bluetooth).
    - Dynamic receipt templates with custom headers/footers.
    - Print preview and immediate post-transaction printing.

### 📊 Analytics & Reporting
- **Interactive Dashboards**: Visualize sales performance with graphic charts (Daily/Monthly revenue).
- **Real-time Tracking**: Monitor every transaction, recent orders, and stock movements.
- **Profit Analysis**: Automatic calculation of revenue and margins based on product tracking.
- **Export/Import**: Bulk manage data with Filament's high-performance Export/Import actions for Products.

### 👥 Customer & Membership
- **Loyalty Program**: Track customer purchase history and management.
- **Member-Only Pricing**: Incentivize repeat business with tiered discount profiles.

### 🔔 Monitoring & Notifications
- **Smart Notifications**: Real-time database and browser notifications for stock alerts and order status.
- **Activity Monitoring**: Track which cashier is active and processing transactions.

---

## 🛠️ Tech Stack

- **Framework**: [Laravel 12.x](https://laravel.com)
- **Admin Panel**: [Filament 3.x](https://filamentphp.com)
- **Frontend**: [Livewire](https://livewire.laravel.com), [Alpine.js](https://alpinejs.dev)
- **Build Tool**: [Vite](https://vitejs.dev)
- **Printer Support**: [`mike42/escpos-php`](https://github.com/mike42/escpos-php) & **PrintHub.js**
- **Environment**: PHP 8.2+, MySQL 8.x or SQLite

---

## 🚀 Installation Guide

Follow these steps to set up the project on your local environment:

### 1. Prerequisites
Ensure you have the following installed:
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- Database (MySQL/PostgreSQL/SQLite)

### 2. Clone the Repository
```bash
git clone https://github.com/your-username/antigravity-pos.git
cd antigravity-pos
```

### 3. Quick Setup (Recommended)
You can use the built-in setup script to handle most tasks:
```bash
composer setup
```
*This command installs dependencies, creates `.env`, generates the app key, runs migrations, and builds frontend assets.*

### 4. Alternative Manual Setup
If you prefer manual installation:
```bash
# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create storage symbolic link (Essential for images)
php artisan storage:link

# Configure your database in .env, then run migrations & seeders
php artisan migrate --seed

# Build assets
npm run build
```

### 5. Running the Application
To start the development server:
```bash
# Run server & queue concurrently
composer dev
```
*Alternatively, run them separately:*
```bash
php artisan serve
php artisan queue:work
npm run dev
```

---

### 6. Default Credentials
Use these accounts to log in after seeding the database:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Administrator** | `admin@pos.com` | `password` |
| **Cashier** | `kasir@pos.com` | `password` |

---

## 🏗️ Essential Commands

| Command | Description |
| :--- | :--- |
| `php artisan queue:work` | **MANDATORY**: Processes product imports, exports, and stock notifications. |
| `php artisan storage:link` | Links the public storage folder (required to display product images). |
| `php artisan optimize:clear` | Clears all application cache (use after configuration changes). |
| `php artisan filament:upgrade` | Ensures Filament assets are up to date after an update. |
| `php artisan make:filament-user` | Interactively create a new administrative user. |
| `composer dev` | Custom command to run server, queue, and vite concurrently. |

---

## 📝 License

This project is open-source software licensed under the [MIT license](LICENSE).
