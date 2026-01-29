# Race Condition Analysis & Fix Plan

Analysis shows that the current implementation has potential race conditions when multiple cashiers process transactions simultaneously, especially with the same items.

## User Review Required

> [!IMPORTANT]
> The fix involves row-level locking (`SELECT ... FOR UPDATE`). While this ensures data integrity, it can slightly increase database wait times under extremely high concurrent load.

> [!WARNING]
> If a transaction fails due to a unique constraint (e.g., invoice number collision despite prefixing), the current UI might show a generic error. I plan to add specific handling for these cases.

## Proposed Changes

### POS Terminal Logic
I will modify the `processPayment` method to ensure data integrity during high concurrency.

#### [MODIFY] [PosTerminal.php](file:///c:/laragon/www/antigravity-pos/app/Livewire/PosTerminal.php)
- Use `lockForUpdate()` when fetching products to reduce stock.
- Wrap the entire transaction logic securely.

### Invoice Number Generation
Currently, `generateInvoiceNumber` reads the last record and increments it. This is prone to collisions.

#### [MODIFY] [Order.php](file:///c:/laragon/www/antigravity-pos/app/Models/Order.php)
- Optimize `generateInvoiceNumber` to be more resilient.
- Use a database-level lock on the last order row for today within the transaction to ensure sequentiality.

#### [MODIFY] [Product.php](file:///c:/laragon/www/antigravity-pos/app/Models/Product.php)
- Update `reduceStock` to potentially accept a locked instance or handle the check more strictly.

## Verification Plan

### Automated Tests
I will create a feature test to simulate concurrent requests if possible, or at least verify the locking behavior.
- Command: `php artisan test --filter=RaceConditionTest`

### Manual Verification
1. Open two browser tabs/sessions as different cashiers.
2. Add the same limited-stock item (1 left) to both carts.
3. Click "Process Payment" on both simultaneously (or as close as possible).
4. Verify one succeeds and the other receives a "Stok tidak mencukupi" or similar error, and stock never goes negative.
5. Verify no duplicate invoice numbers are generated.
