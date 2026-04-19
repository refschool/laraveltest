<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock',
        'description',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
    ];

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function getPriceWithTaxAttribute(): float
    {
        return round($this->price * 1.20, 2);
    }

    public function decreaseStock(int $quantity): void
    {
        if ($quantity > $this->stock) {
            throw new \InvalidArgumentException("Stock insuffisant.");
        }
        $this->stock -= $quantity;
        $this->save();
    }
}
