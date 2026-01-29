<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock',
        'unlimited_stock',
        'track_cost',
        'image',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'unlimited_stock' => 'boolean',
        'track_cost' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category of this product
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get order items containing this product
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if stock is low (at or below minimum)
     */
    public function isLowStock(): bool
    {
        if ($this->unlimited_stock) {
            return false;
        }
        return $this->stock <= $this->min_stock;
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock(): bool
    {
        if ($this->unlimited_stock) {
            return false;
        }
        return $this->stock <= 0;
    }

    /**
     * Calculate profit margin
     */
    public function getProfitAttribute(): float
    {
        if (!$this->track_cost) {
            return 0;
        }
        return $this->selling_price - $this->purchase_price;
    }

    /**
     * Calculate profit percentage
     */
    public function getProfitPercentageAttribute(): float
    {
        if ($this->purchase_price <= 0) {
            return 0;
        }
        return (($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100;
    }

    /**
     * Reduce stock after sale
     */
    public function reduceStock(int $quantity): bool
    {
        // Skip stock reduction for unlimited stock products
        if ($this->unlimited_stock) {
            return true;
        }

        if ($this->stock >= $quantity) {
            $this->decrement('stock', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Increase stock (restock)
     */
    public function addStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }

    /**
     * Scope to get only active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get products with low stock (excludes unlimited stock)
     */
    public function scopeLowStock($query)
    {
        return $query->where('unlimited_stock', false)
            ->whereColumn('stock', '<=', 'min_stock');
    }

    /**
     * Scope to get products with limited stock tracking
     */
    public function scopeLimitedStock($query)
    {
        return $query->where('unlimited_stock', false);
    }

    /**
     * Scope to search products by name, sku, or barcode
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%");
        });
    }
}
