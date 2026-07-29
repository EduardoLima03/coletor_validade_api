<?php

namespace Tests\Feature;

use App\Models\Barcode;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['position' => 'ADMIN']);
        $this->token = $this->admin->createToken('test')->plainTextToken;
    }

    public function test_index_retorna_barcodes_paginados()
    {
        Product::factory()->hasBarcodes(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/ean');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'links']);
    }

    public function test_store_cria_barcode()
    {
        $product = Product::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/ean', [
            'ean' => '1234567890123',
            'product_id' => $product->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('barcodes', ['ean' => '1234567890123']);
    }

    public function test_show_retorna_barcode()
    {
        $barcode = Barcode::factory()->create();

        $response = $this->withToken($this->token)->getJson("/api/ean/{$barcode->id}");

        $response->assertOk();
        $response->assertJson(['ean' => $barcode->ean]);
    }

    public function test_update_barcode()
    {
        $barcode = Barcode::factory()->create();
        $newProduct = Product::factory()->create();

        $response = $this->withToken($this->token)->putJson("/api/ean/{$barcode->id}", [
            'ean' => $barcode->ean,
            'product_id' => $newProduct->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('barcodes', ['id' => $barcode->id, 'product_id' => $newProduct->id]);
    }

    public function test_destroy_barcode()
    {
        $barcode = Barcode::factory()->create();

        $response = $this->withToken($this->token)->deleteJson("/api/ean/{$barcode->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('barcodes', ['id' => $barcode->id]);
    }

    public function test_findByEan_retorna_produto()
    {
        $product = Product::factory()->create(['code' => '55555', 'description' => 'Produto EAN']);
        Barcode::factory()->create(['ean' => '9999999999999', 'product_id' => $product->id]);

        $response = $this->withToken($this->token)->getJson('/api/by-ean/9999999999999');

        $response->assertOk();
        $response->assertJson([
            'code' => '55555',
            'description' => 'Produto EAN',
            'ean' => '9999999999999',
        ]);
    }

    public function test_findByEan_inexistente_retorna_404()
    {
        $response = $this->withToken($this->token)->getJson('/api/by-ean/0000000000000');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Produto nao encontrado']);
    }

    public function test_saveAll_cria_multiplos_barcodes()
    {
        $product = Product::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/ean-save-all', [
            ['ean' => '1111111111111', 'product_id' => $product->id],
            ['ean' => '2222222222222', 'product_id' => $product->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('barcodes', 2);
    }
}
