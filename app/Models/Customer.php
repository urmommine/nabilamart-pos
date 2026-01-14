<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'default_discount_percentage',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function specialDiscounts(): HasMany
    {
        return $this->hasMany(CustomerProductDiscount::class);
    }
}
