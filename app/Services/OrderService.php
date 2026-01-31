<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

class OrderService
{
    /**
     * Process a new order with transaction and row-level locking.
     *
     * @param array $data
     * @param array $cartItems
     * @return Order
     * @throws \Exception
     */
    public function createOrder(array $data, array $cartItems): Order
    {
        return DB::transaction(function () use ($data, $cartItems) {
            try {
                // 1. Create the order
                // Invoice number is generated in the Order model 'creating' event via generateInvoiceNumber()
                // which already uses lockForUpdate() to prevent collisions.
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'subtotal' => $data['subtotal'],
                    'discount' => $data['discount'],
                    'tax' => $data['tax'],
                    'total_amount' => $data['total_amount'],
                    'payment_method' => $data['payment_method'],
                    'amount_paid' => $data['amount_paid'],
                    'change' => $data['change'],
                    'payment_status' => 'paid',
                    'customer_id' => $data['customer_id'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                // 2. Process items and update stock
                foreach ($cartItems as $item) {
                    // Fetch product with lock for update to prevent race conditions
                    $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                    if (!$product) {
                        throw new \Exception("Produk '{$item['name']}' tidak ditemukan.");
                    }

                    // Skip stock validation for unlimited stock products
                    if (!$product->unlimited_stock && $product->stock < $item['quantity']) {
                        throw new \Exception("Stok produk '{$product->name}' tidak mencukupi (Tersisa: {$product->stock}).");
                    }

                    // Create order item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['total'],
                        'discount_info' => $item['discount_info'] ?? null,
                    ]);

                    // Deduct stock only for limited stock products
                    if (!$product->unlimited_stock) {
                        $product->stock -= $item['quantity'];
                        $product->save();
                    }
                }

                return $order;

            } catch (QueryException $e) {
                // Handle unique constraint violations (likely invoice number collisions)
                if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                    throw new \Exception('Gagal membuat invoice. Konteks transaksi sedang padat, silakan coba lagi sebentar.');
                }
                throw $e;
            }
        });
    }
}
