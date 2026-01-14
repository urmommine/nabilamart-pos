<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'payment_method',
        'amount_paid',
        'change',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate invoice number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->invoice_number)) {
                $order->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate unique invoice number
     * Format: INV-YYYYMMDD-XXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $today = Carbon::today();
        $prefix = 'INV-' . $today->format('Ymd') . '-';
        
        // Get the last order of today
        $lastOrder = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastOrder) {
            // Extract the number and increment
            $lastNumber = (int) substr($lastOrder->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the kasir who processed this order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer associated with the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Alias for user relationship
     */
    public function kasir(): BelongsTo
    {
        return $this->user();
    }

    /**
     * Get order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(): void
    {
        $this->update(['payment_status' => 'paid']);
    }

    /**
     * Calculate total profit from this order
     */
    public function calculateProfit(): float
    {
        return $this->items->sum(function ($item) {
            $product = $item->product;
            if ($product) {
                return ($item->unit_price - $product->purchase_price) * $item->quantity;
            }
            return 0;
        });
    }

    /**
     * Scope to get orders for today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    /**
     * Scope to get orders for this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    /**
     * Scope to get orders for this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                     ->whereYear('created_at', Carbon::now()->year);
    }

    /**
     * Scope to get paid orders only
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}
