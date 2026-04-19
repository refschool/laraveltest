<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Services\CartService;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cart = new CartService();
    }

    private function makeProduct(int $id, float $price, int $stock = 10, string $name = 'Produit'): Product
    {
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['isInStock'])
            ->getMock();
        $product->id    = $id;
        $product->name  = $name;
        $product->price = $price;
        $product->stock = $stock;
        $product->method('isInStock')->willReturn($stock > 0);

        return $product;
    }

    /** @test */
    public function cart_is_empty_by_default(): void
    {
        $this->assertTrue($this->cart->isEmpty());
        $this->assertEquals(0, $this->cart->getItemCount());
    }

    /** @test */
    public function it_adds_a_product_to_the_cart(): void
    {
        $product = $this->makeProduct(1, 50.00);

        $this->cart->addItem($product);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(1, $this->cart->getItemCount());
    }

    /** @test */
    public function it_cumulates_quantity_when_same_product_added_twice(): void
    {
        $product = $this->makeProduct(1, 50.00);

        $this->cart->addItem($product, 2);
        $this->cart->addItem($product, 3);

        $this->assertEquals(5, $this->cart->getItemCount());
    }

    /** @test */
    public function it_calculates_total_correctly(): void
    {
        $p1 = $this->makeProduct(1, 10.00);
        $p2 = $this->makeProduct(2, 25.00);

        $this->cart->addItem($p1, 2);
        $this->cart->addItem($p2, 1);

        $this->assertEquals(45.00, $this->cart->getTotal());
    }

    /** @test */
    public function it_calculates_total_with_tax(): void
    {
        $product = $this->makeProduct(1, 100.00);

        $this->cart->addItem($product);

        $this->assertEquals(120.00, $this->cart->getTotalWithTax());
    }

    /** @test */
    public function it_removes_a_product_from_cart(): void
    {
        $product = $this->makeProduct(1, 50.00);
        $this->cart->addItem($product);

        $this->cart->removeItem(1);

        $this->assertTrue($this->cart->isEmpty());
    }

    /** @test */
    public function it_clears_the_cart(): void
    {
        $this->cart->addItem($this->makeProduct(1, 10.00));
        $this->cart->addItem($this->makeProduct(2, 20.00));

        $this->cart->clear();

        $this->assertTrue($this->cart->isEmpty());
    }

    /** @test */
    public function it_throws_exception_when_adding_out_of_stock_product(): void
    {
        $product = $this->makeProduct(1, 50.00, 0, 'Produit épuisé');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('en rupture de stock');

        $this->cart->addItem($product);
    }

    /** @test */
    public function it_throws_exception_when_quantity_is_zero_or_negative(): void
    {
        $product = $this->makeProduct(1, 50.00);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('supérieure à 0');

        $this->cart->addItem($product, 0);
    }
}
