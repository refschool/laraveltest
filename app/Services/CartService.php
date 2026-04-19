<?php

namespace App\Services;

use App\Models\Product;

class CartService
{
    private array $items = [];

    public function addItem(Product $product, int $quantity = 1): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException("La quantité doit être supérieure à 0.");
        }

        if (!$product->isInStock()) {
            throw new \RuntimeException("Le produit '{$product->name}' est en rupture de stock.");
        }

        $id = $product->id;

        if (isset($this->items[$id])) {
            $this->items[$id]['quantity'] += $quantity;
        } else {
            $this->items[$id] = ['product' => $product, 'quantity' => $quantity];
        }
    }

    public function removeItem(int $productId): void
    {
        unset($this->items[$productId]);
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['product']->price * $item['quantity'];
        }
        return round($total, 2);
    }

    public function getTotalWithTax(): float
    {
        return round($this->getTotal() * 1.20, 2);
    }

    public function getItemCount(): int
    {
        return array_sum(array_column($this->items, 'quantity'));
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function clear(): void
    {
        $this->items = [];
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
