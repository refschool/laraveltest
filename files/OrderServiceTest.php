<?php

namespace Tests\Unit;

use App\Services\OrderService;
use Tests\TestCase;

/**
 * Tests du service OrderService.
 * Illustre les tests de règles métier complexes avec data providers.
 */
class OrderServiceTest extends TestCase
{
    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderService();
    }

    // ---------------------------------------------------------------
    // calculateSubtotal()
    // ---------------------------------------------------------------

    public function test_subtotal_article_unique(): void
    {
        $items = [['price' => 10.0, 'quantity' => 3]];
        $this->assertSame(30.0, $this->service->calculateSubtotal($items));
    }

    public function test_subtotal_plusieurs_articles(): void
    {
        $items = [
            ['price' => 10.0, 'quantity' => 2],
            ['price' => 5.50, 'quantity' => 4],
        ];
        // 20 + 22 = 42
        $this->assertSame(42.0, $this->service->calculateSubtotal($items));
    }

    public function test_subtotal_panier_vide_retourne_zero(): void
    {
        $this->assertSame(0.0, $this->service->calculateSubtotal([]));
    }

    public function test_subtotal_leve_exception_si_prix_negatif(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->calculateSubtotal([['price' => -5.0, 'quantity' => 1]]);
    }

    public function test_subtotal_leve_exception_si_quantite_zero(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->calculateSubtotal([['price' => 10.0, 'quantity' => 0]]);
    }

    // ---------------------------------------------------------------
    // calculateShipping()
    // ---------------------------------------------------------------

    /** @dataProvider shippingProvider */
    public function test_frais_de_livraison(float $subtotal, float $expected): void
    {
        $this->assertSame($expected, $this->service->calculateShipping($subtotal));
    }

    public static function shippingProvider(): array
    {
        return [
            'en dessous du seuil'  => [30.0, 5.90],
            'exactement au seuil'  => [50.0, 0.0],
            'au dessus du seuil'   => [80.0, 0.0],
            'panier vide'          => [0.0,  5.90],
        ];
    }

    // ---------------------------------------------------------------
    // calculateTax()
    // ---------------------------------------------------------------

    public function test_tva_est_vingt_pourcent(): void
    {
        $this->assertSame(20.0, $this->service->calculateTax(100.0));
    }

    public function test_tva_arrondie_a_deux_decimales(): void
    {
        $this->assertSame(2.0, $this->service->calculateTax(10.0));
    }

    // ---------------------------------------------------------------
    // applyPromoCode()
    // ---------------------------------------------------------------

    /** @dataProvider promoCodeProvider */
    public function test_code_promo_applique_la_bonne_reduction(
        string $code,
        float  $subtotal,
        float  $expected
    ): void {
        $this->assertSame($expected, $this->service->applyPromoCode($subtotal, $code));
    }

    public static function promoCodeProvider(): array
    {
        return [
            'BIENVENUE10 sur 100€' => ['BIENVENUE10', 100.0, 90.0],
            'SOLDES20 sur 100€'    => ['SOLDES20',    100.0, 80.0],
            'VIP50 sur 100€'       => ['VIP50',       100.0, 50.0],
            'code insensible casse'=> ['bienvenue10', 100.0, 90.0],
        ];
    }

    public function test_code_promo_invalide_leve_une_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Code promo invalide');

        $this->service->applyPromoCode(100.0, 'FAUX');
    }

    // ---------------------------------------------------------------
    // calculateTotal() — scénarios complets
    // ---------------------------------------------------------------

    public function test_total_sans_promo_sous_le_seuil_de_livraison_gratuite(): void
    {
        $items = [['price' => 20.0, 'quantity' => 1]]; // 20€ HT

        $result = $this->service->calculateTotal($items);

        $this->assertSame(20.0,  $result['subtotal']);
        $this->assertSame(4.0,   $result['tax']);      // 20% de 20
        $this->assertSame(5.90,  $result['shipping']); // < 50€
        $this->assertSame(29.90, $result['total']);
    }

    public function test_total_avec_promo_et_livraison_gratuite(): void
    {
        $items = [['price' => 100.0, 'quantity' => 1]]; // 100€ HT

        $result = $this->service->calculateTotal($items, 'SOLDES20');

        $this->assertSame(80.0,  $result['subtotal']);  // -20%
        $this->assertSame(16.0,  $result['tax']);       // 20% de 80
        $this->assertSame(0.0,   $result['shipping']);  // > 50€
        $this->assertSame(96.0,  $result['total']);
    }

    public function test_total_retourne_les_bonnes_cles(): void
    {
        $result = $this->service->calculateTotal([['price' => 10.0, 'quantity' => 1]]);

        $this->assertArrayHasKey('subtotal', $result);
        $this->assertArrayHasKey('tax', $result);
        $this->assertArrayHasKey('shipping', $result);
        $this->assertArrayHasKey('total', $result);
    }
}
