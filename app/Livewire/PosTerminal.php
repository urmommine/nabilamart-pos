<?php

namespace App\Livewire;

use App\Http\Controllers\ReceiptController;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Customer;
use App\Models\CustomerProductDiscount;
use App\Services\OrderService;

#[Layout('layouts.pos')]
class PosTerminal extends Component
{
    // Search and filter
    public string $search = '';
    public ?int $selectedCategory = null;
    public int $perPage = 30;

    // Customer
    public ?int $selectedCustomerId = null;
    public ?Customer $customer = null;
    public array $customerSearchResults = [];
    public string $customerSearch = '';

    // Cart
    public array $cart = [];
    public float $subtotal = 0;
    public float $discount = 0;
    public float $discountType = 0; // 0 = nominal, 1 = percentage
    public $discountValue = '';
    public float $tax = 0;
    public float $defaultTax = 0;
    public float $total = 0;

    // Checkout modal
    public bool $showCheckoutModal = false;
    public string $paymentMethod = 'cash';
    public $amountPaid = '';
    public $change = 0;

    // Discount modal (Global)
    public bool $showDiscountModal = false;

    // Item Discount Modal
    public bool $showItemDiscountModal = false;
    public ?int $editingCartIndex = null;
    public $itemDiscountValue = '';
    public int $itemDiscountType = 0; // 0 = nominal, 1 = percentage

    // Profile modal
    public bool $showProfileModal = false;
    public string $profileName = '';
    public string $profileEmail = '';
    public string $profilePassword = '';
    public string $profilePasswordConfirmation = '';

    // Printer
    public string $printerType = 'usb';

    public function mount()
    {
        // Load tax percentage from settings
        $this->defaultTax = (float) StoreSetting::get(StoreSetting::TAX_PERCENTAGE, 0);
        $this->tax = $this->defaultTax;
        $this->printerType = StoreSetting::get(StoreSetting::PRINTER_TYPE, 'usb');
    }

    #[Computed]
    public function categories()
    {
        return Cache::remember('pos_categories', 600, function () {
            return Category::active()->withCount('products')->get();
        });
    }

    public function openProfileModal()
    {
        $user = Auth::user();
        $this->profileName = $user->name;
        $this->profileEmail = $user->email;
        $this->profilePassword = '';
        $this->profilePasswordConfirmation = '';
        $this->showProfileModal = true;
    }

    public function updateProfile()
    {
        $this->validate([
            'profileName' => 'required|string|max:255',
            'profileEmail' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'profilePassword' => 'nullable|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->name = $this->profileName;
        $user->email = $this->profileEmail;

        if (!empty($this->profilePassword)) {
            $user->password = bcrypt($this->profilePassword);
        }

        $user->save();

        $this->showProfileModal = false;
        $this->dispatch('notify', type: 'success', message: 'Profil berhasil diperbarui');
    }

    public function render()
    {
        $products = Product::query()
            ->active()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->orderBy('name')
            ->take($this->perPage)
            ->get();

        return view('livewire.pos-terminal', [
            'categories' => $this->categories,
            'products' => $products,
            'storeName' => StoreSetting::get(StoreSetting::STORE_NAME, 'POS Store'),
            'customers' => $this->customerSearch ? Customer::where('name', 'like', '%' . $this->customerSearch . '%')->limit(5)->get() : [],
        ]);
    }

    public function selectCategory(?int $categoryId)
    {
        $this->selectedCategory = $categoryId === $this->selectedCategory ? null : $categoryId;
        $this->perPage = 30; // Reset pagination
    }

    public function updatedSearch()
    {
        $this->perPage = 30; // Reset pagination
    }

    public function handleBarcodeScan($barcode = null)
    {
        // Use passed barcode or fallback to search property
        $code = $barcode ?? $this->search;

        if (empty($code)) {
            return;
        }

        $product = Product::query()
            ->active()
            ->where('barcode', $code)
            ->first();

        if ($product) {
            $this->addToCart($product->id);
            $this->search = ''; // Clear search after successful add
            $this->dispatch('clear-search');
        } else {
            $this->dispatch('notify', type: 'error', message: 'Produk tidak ditemukan');
            $this->search = ''; // Clear search even if not found
            $this->dispatch('clear-search');
        }
    }

    public function loadMore()
    {
        $this->perPage += 30;
    }

    public function addToCart(int $productId)
    {
        $product = Product::find($productId);

        if (!$product || !$product->is_active) {
            $this->dispatch('notify', type: 'error', message: 'Produk tidak ditemukan');
            return;
        }

        if (!$product->unlimited_stock && $product->stock <= 0) {
            $this->dispatch('notify', type: 'error', message: 'Stok produk habis');
            return;
        }

        // Check if already in cart
        $cartKey = array_search($productId, array_column($this->cart, 'product_id'));

        if ($cartKey !== false) {
            // Check stock before increasing (skip for unlimited stock)
            if (!$product->unlimited_stock && $this->cart[$cartKey]['quantity'] >= $product->stock) {
                $this->dispatch('notify', type: 'warning', message: 'Stok tidak mencukupi');
                return;
            }
            $this->cart[$cartKey]['quantity']++;
            $this->cart[$cartKey]['total'] = $this->cart[$cartKey]['quantity'] * $this->cart[$cartKey]['price'];
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'original_price' => (float) $product->selling_price,
                'price' => (float) $product->selling_price,
                'quantity' => 1,
                'total' => (float) $product->selling_price,
                'image' => $product->image,
                'stock' => $product->stock,
                'unlimited_stock' => $product->unlimited_stock,
                'discount_info' => null, // 'Global 10%' or 'Special $5'
                'manual_discount' => false, // key for manual override
            ];
        }

        // Re-apply discounts whenever cart changes
        $this->applyCustomerDiscounts();
        $this->calculateTotals();
        $this->dispatch('notify', type: 'success', message: $product->name . ' ditambahkan');
    }

    public function updatedSelectedCustomerId()
    {
        if ($this->selectedCustomerId) {
            $this->customer = Customer::with('specialDiscounts')->find($this->selectedCustomerId);
            $this->customerSearch = $this->customer->name;
        } else {
            $this->customer = null;
            $this->customerSearch = '';
        }
        $this->applyCustomerDiscounts();
        $this->calculateTotals();
    }

    public function selectCustomer($id)
    {
        $this->selectedCustomerId = $id;
        $this->updatedSelectedCustomerId();
    }

    public function applyCustomerDiscounts()
    {
        if (empty($this->cart))
            return;

        // Reset all to original price first
        foreach ($this->cart as &$item) {
            $item['price'] = $item['original_price'];
            $item['discount_info'] = null;
        }
        unset($item);

        if (!$this->customer) {
            // Recalculate totals based on original prices
            foreach ($this->cart as &$item) {
                $item['total'] = $item['quantity'] * $item['price'];
            }
            return;
        }

        $globalDiscount = (float) $this->customer->default_discount_percentage;
        $specialDiscounts = $this->customer->specialDiscounts->keyBy('product_id');

        foreach ($this->cart as &$item) {
            // Skip manual overrides
            if (isset($item['manual_discount']) && $item['manual_discount']) {
                $item['total'] = $item['quantity'] * $item['price'];
                continue;
            }

            $productId = $item['product_id'];
            $newPrice = $item['original_price'];
            $info = null;

            if ($specialDiscounts->has($productId)) {
                $discount = $specialDiscounts[$productId];
                if ($discount->discount_type === 'fixed') {
                    $newPrice = max(0, $item['original_price'] - $discount->discount_value);
                    $info = 'Special -Rp' . number_format($discount->discount_value, 0);
                } else {
                    $newPrice = max(0, $item['original_price'] * (1 - ($discount->discount_value / 100)));
                    $info = 'Special -' . $discount->discount_value . '%';
                }
            } elseif ($globalDiscount > 0) {
                $newPrice = max(0, $item['original_price'] * (1 - ($globalDiscount / 100)));
                $info = 'Member -' . $globalDiscount . '%';
            }

            $item['price'] = $newPrice;
            $item['discount_info'] = $info;
            $item['total'] = $item['quantity'] * $item['price'];
        }
    }

    #[On('barcodeScanned')]
    public function handleBarcode(string $barcode)
    {
        $product = Product::where('barcode', $barcode)->first();

        if ($product) {
            $this->addToCart($product->id);
        } else {
            $this->dispatch('notify', type: 'error', message: 'Produk dengan barcode ' . $barcode . ' tidak ditemukan');
        }
    }

    public function incrementQuantity(int $index)
    {
        if (isset($this->cart[$index])) {
            $product = Product::find($this->cart[$index]['product_id']);

            // Allow increment if unlimited stock or quantity is below stock
            if ($product && ($product->unlimited_stock || $this->cart[$index]['quantity'] < $product->stock)) {
                $this->cart[$index]['quantity']++;
                $this->cart[$index]['total'] = $this->cart[$index]['quantity'] * $this->cart[$index]['price'];
                $this->applyCustomerDiscounts();
                $this->calculateTotals();
            } else {
                $this->dispatch('notify', type: 'warning', message: 'Stok tidak mencukupi');
            }
        }
    }

    public function decrementQuantity(int $index)
    {
        if (isset($this->cart[$index])) {
            if ($this->cart[$index]['quantity'] > 1) {
                $this->cart[$index]['quantity']--;
                $this->cart[$index]['total'] = $this->cart[$index]['quantity'] * $this->cart[$index]['price'];
            } else {
                $this->removeFromCart($index);
            }
            $this->calculateTotals();
        }
    }

    public function removeFromCart(int $index)
    {
        if (isset($this->cart[$index])) {
            $name = $this->cart[$index]['name'];
            unset($this->cart[$index]);
            $this->cart = array_values($this->cart); // Re-index array
            $this->calculateTotals();
            $this->dispatch('notify', type: 'success', message: $name . ' dihapus dari keranjang');
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->discount = 0;
        $this->discountValue = '';
        $this->calculateTotals();
        $this->dispatch('notify', type: 'info', message: 'Keranjang dikosongkan');
    }

    public function calculateTotals()
    {
        $this->subtotal = array_sum(array_column($this->cart, 'total'));

        // Calculate discount
        if ($this->discountType == 1 && (float) $this->discountValue > 0) {
            // Percentage discount
            $this->discount = $this->subtotal * ((float) $this->discountValue / 100);
        } else {
            $this->discount = (float) $this->discountValue;
        }

        // Calculate total (with tax if applicable)
        $afterDiscount = $this->subtotal - $this->discount;
        $taxAmount = $this->tax > 0 ? $afterDiscount * ($this->tax / 100) : 0;
        $this->total = $afterDiscount + $taxAmount;

        // Update change if checkout modal is open
        if ($this->showCheckoutModal) {
            $this->calculateChange();
        }
    }

    #[On('openCheckout')]
    public function openCheckout()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', type: 'warning', message: 'Keranjang masih kosong');
            return;
        }

        $this->amountPaid = '';
        $this->change = 0;
        $this->paymentMethod = 'cash';
        $this->showCheckoutModal = true;
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->showCheckoutModal = false;
        $this->showDiscountModal = false;
        $this->showItemDiscountModal = false;
        $this->showProfileModal = false;
        $this->editingCartIndex = null;
    }

    public function setPaymentMethod(string $method)
    {
        $this->paymentMethod = $method;

        // For non-cash, set amount paid to exact total
        if ($method !== 'cash') {
            $this->amountPaid = $this->total;
            $this->change = 0;
        }
    }

    public function setQuickAmount(float $amount)
    {
        $this->amountPaid = $amount;
        $this->calculateChange();
    }

    public function setExactAmount()
    {
        $this->amountPaid = $this->total;
        $this->change = 0;
    }

    public function updatedAmountPaid()
    {
        $this->calculateChange();
    }

    public function calculateChange()
    {
        $this->change = max(0, (float) $this->amountPaid - $this->total);
    }

    public function openDiscountModal()
    {
        $this->showDiscountModal = true;
    }

    public function applyDiscount()
    {
        $this->calculateTotals();
        $this->showDiscountModal = false;
        $this->dispatch('notify', type: 'success', message: 'Diskon diterapkan');
    }

    public function openItemDiscountModal($index)
    {
        if (isset($this->cart[$index])) {
            $this->editingCartIndex = $index;
            $this->itemDiscountType = 0;
            $this->itemDiscountValue = '';
            $this->showItemDiscountModal = true;
        }
    }

    public function applyItemDiscount()
    {
        if ($this->editingCartIndex !== null && isset($this->cart[$this->editingCartIndex])) {
            $index = $this->editingCartIndex;
            $originalPrice = $this->cart[$index]['original_price'];
            $newPrice = $originalPrice;
            $info = '';

            if ((float) $this->itemDiscountValue > 0) {
                if ($this->itemDiscountType == 1) {
                    // Percentage
                    $newPrice = max(0, $originalPrice * (1 - ((float) $this->itemDiscountValue / 100)));
                    $info = 'Manual -' . $this->itemDiscountValue . '%';
                } else {
                    // Fixed
                    $newPrice = max(0, $originalPrice - (float) $this->itemDiscountValue);
                    $info = 'Manual -Rp' . number_format((float) $this->itemDiscountValue, 0);
                }
                $this->cart[$index]['price'] = $newPrice;
                $this->cart[$index]['discount_info'] = $info;
                $this->cart[$index]['manual_discount'] = true;
            } else {
                // Remove manual discount if value is 0 or empty
                $this->cart[$index]['price'] = $originalPrice;
                $this->cart[$index]['discount_info'] = null;
                $this->cart[$index]['manual_discount'] = false;

                // Re-apply auto discounts if any
                if ($this->customer) {
                    $this->applyCustomerDiscounts();
                }
            }

            $this->cart[$index]['total'] = $this->cart[$index]['quantity'] * $this->cart[$index]['price'];
            $this->calculateTotals();
            $this->closeModal();
            $this->dispatch('notify', type: 'success', message: 'Harga item diperbarui');
        }
    }

    public function toggleTax()
    {
        if ($this->tax > 0) {
            $this->tax = 0;
            $this->dispatch('notify', type: 'info', message: 'Pajak dinonaktifkan');
        } else {
            $this->tax = $this->defaultTax;
            $this->dispatch('notify', type: 'success', message: 'Pajak diaktifkan (' . $this->tax . '%)');
        }
        $this->calculateTotals();
    }

    public function processPayment(OrderService $orderService)
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', type: 'error', message: 'Keranjang kosong');
            return;
        }

        if ($this->paymentMethod === 'cash' && (float) $this->amountPaid < $this->total) {
            $this->dispatch('notify', type: 'error', message: 'Jumlah bayar kurang dari total');
            return;
        }

        try {
            $orderData = [
                'subtotal' => $this->subtotal,
                'discount' => $this->discount,
                'tax' => $this->tax > 0 ? ($this->subtotal - $this->discount) * ($this->tax / 100) : 0,
                'total_amount' => $this->total,
                'payment_method' => $this->paymentMethod,
                'amount_paid' => (float) $this->amountPaid,
                'change' => $this->change,
                'customer_id' => $this->selectedCustomerId,
            ];

            $order = $orderService->createOrder($orderData, $this->cart);

            // Clear cart
            $this->cart = [];
            $this->discount = 0;
            $this->discountValue = '';
            $this->calculateTotals();
            $this->showCheckoutModal = false;

            // Trigger print
            if ($this->printerType === 'bluetooth' || $this->printerType === 'usb_web') {
                $this->dispatch('printBluetoothReceipt', data: $this->getReceiptData($order));
            } else {
                $this->dispatch('triggerDirectPrint', orderId: $order->id);
            }

            $this->dispatch('notify', type: 'success', message: 'Transaksi berhasil! Invoice: ' . $order->invoice_number);

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }
    protected function getReceiptData(Order $order)
    {
        $order->load(['items', 'customer']);
        return [
            'storeName' => StoreSetting::get(StoreSetting::STORE_NAME, 'POS Store'),
            'storeAddress' => StoreSetting::get(StoreSetting::STORE_ADDRESS, ''),
            'storePhone' => StoreSetting::get(StoreSetting::STORE_PHONE, ''),
            'invoice' => $order->invoice_number,
            'date' => $order->created_at->format('d-m-Y H:i'),
            'cashier' => Auth::user()->name,
            'customer' => $order->customer ? $order->customer->name : 'Walk-in Customer',
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product_name,
                    'qty' => $item->quantity,
                    'price' => $item->unit_price,
                    'total' => $item->total_price,
                ];
            }),
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'tax' => $order->tax,
            'total' => $order->total_amount,
            'payment_method' => ucfirst($order->payment_method),
            'amount_paid' => $order->amount_paid,
            'change' => $order->change,
            'footer' => StoreSetting::get(StoreSetting::RECEIPT_FOOTER, 'Terima Kasih'),
        ];
    }
}
