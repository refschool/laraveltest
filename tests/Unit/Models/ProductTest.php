<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private function makeProduct(array $attributes = []): Product
    {
        $product = new Product();
        $product->fill(array_merge([
            'name'  => 'Produit Test',
            'price' => 100.00,
            'stock' => 10,
        ], $attributes));

        return $product;
    }

    /** @test */
    public function it_returns_true_when_product_is_in_stock(): void
    {
        $product = $this->makeProduct(['stock' => 5]);

        $this->assertTrue($product->isInStock());
    }

    /** @test */
    public function it_returns_false_when_product_is_out_of_stock(): void
    {
        $product = $this->makeProduct(['stock' => 0]);

        $this->assertFalse($product->isInStock());
    }

    /** @test */
    public function it_calculates_price_with_tax_correctly(): void
    {
        $product = $this->makeProduct(['price' => 100.00]);

        $this->assertEquals(120.00, $product->price_with_tax);
    }

    /** @test */
    public function it_rounds_price_with_tax_to_two_decimals(): void
    {
        $product = $this->makeProduct(['price' => 9.99]);

        $this->assertEquals(11.99, $product->price_with_tax);
    }

    /** @test */
    public function it_decreases_stock_correctly(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['save'])
            ->getMock();
        $product->stock = 10;
        $product->expects($this->once())->method('save');

        $product->decreaseStock(3);

        $this->assertEquals(7, $product->stock);
    }

    /** @test */
    public function it_throws_exception_when_decreasing_more_than_available_stock(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['save'])
            ->getMock();
        $product->stock = 5;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Stock insuffisant.');

        $product->decreaseStock(10);
    }
}
