<?php

namespace Tests\Unit\Services;

use App\Services\DiscountService;
use PHPUnit\Framework\TestCase;

class DiscountServiceTest extends TestCase
{
    private DiscountService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DiscountService();
    }

    /** @test */
    public function it_applies_10_percent_discount(): void
    {
        $this->assertEquals(90.00, $this->service->apply(100.00, 'PROMO10'));
    }

    /** @test */
    public function it_applies_20_percent_discount(): void
    {
        $this->assertEquals(80.00, $this->service->apply(100.00, 'PROMO20'));
    }

    /** @test */
    public function it_is_case_insensitive(): void
    {
        $this->assertEquals(90.00, $this->service->apply(100.00, 'promo10'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Code promo 'INVALID' invalide.");

        $this->service->apply(100.00, 'INVALID');
    }

    /** @test */
    public function it_validates_correct_codes(): void
    {
        $this->assertTrue($this->service->isValid('PROMO10'));
        $this->assertTrue($this->service->isValid('BIENVENU'));
        $this->assertFalse($this->service->isValid('FAKE'));
    }

    /** @test */
    public function it_returns_discount_percentage(): void
    {
        $this->assertEquals(20, $this->service->getDiscountPercent('PROMO20'));
        $this->assertEquals(15, $this->service->getDiscountPercent('BIENVENU'));
    }

    /** @test */
    public function it_rounds_result_to_two_decimals(): void
    {
        $this->assertEquals(89.99, $this->service->apply(99.99, 'PROMO10'));
    }

    /**
     * @test
     * @dataProvider discountProvider
     */
    public function it_applies_discounts_correctly(string $code, float $amount, float $expected): void
    {
        $this->assertEquals($expected, $this->service->apply($amount, $code));
    }

    public static function discountProvider(): array
    {
        return [
            'PROMO10 sur 200€'  => ['PROMO10', 200.00, 180.00],
            'PROMO20 sur 50€'   => ['PROMO20', 50.00, 40.00],
            'BIENVENU sur 100€' => ['BIENVENU', 100.00, 85.00],
        ];
    }
}
