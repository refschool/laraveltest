<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_lists_all_products(): void
    {
        Product::factory()->count(3)->create();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_returns_a_single_product(): void
    {
        $product = Product::factory()->create(['name' => 'Mon Produit', 'price' => 29.99]);

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonFragment(['name' => 'Mon Produit']);
    }

    /** @test */
    public function it_requires_authentication_to_create_a_product(): void
    {
        $this->postJson('/api/products', ['name' => 'Test', 'price' => 10, 'stock' => 1])
            ->assertUnauthorized();
    }

    /** @test */
    public function authenticated_user_can_create_a_product(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/products', [
                'name'        => 'Nouveau produit',
                'price'       => 49.99,
                'stock'       => 20,
                'description' => 'Une super description',
            ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Nouveau produit']);

        $this->assertDatabaseHas('products', ['name' => 'Nouveau produit']);
    }

    /** @test */
    public function it_validates_required_fields_on_create(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/products', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'stock']);
    }

    /** @test */
    public function it_can_delete_a_product(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/products/{$product->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
