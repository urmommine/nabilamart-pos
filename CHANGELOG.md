# Changelog

All notable changes to this project will be documented in this file.

## [v1.0.0] - 2026-02-02

### Added
- **UI/UX**: Added "Printer Gesture Required" notification system to handle browser security restrictions for auto-connecting Bluetooth and USB printers.
- **Printer**: Enhanced `autoConnectPrinter` logic with robust device patching and specific error handling for Access Denied/Security errors.
- **Keyboard**: Full support for `ENTER` key to trigger "Proses Pembayaran" from the checkout modal.
- **Database**: Added `unpaid` and `debt` options to the `payment_status` enum in the `orders` table (Migration: `2026_02_02_000001_modify_payment_status_in_orders_table.php`).
- **OrderResource**: Enabled `EditOrder` page functionality, allowing modifications to existing orders.

### Modified
- **PosTerminal**: Updated logic for payment status validation and processing.
- **OrderService**: Refined order service logic to support new payment statuses.
- **Views**: Updated `pos-terminal.blade.php` to reflect backend changes, improve UI aesthetics, and localized checkout labels to Indonesian.
- **Migration**: Added backticks to column names for safety and used `Schema::disableForeignKeyConstraints()` for better compatibility.
