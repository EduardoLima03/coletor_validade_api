<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
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

    public function test_index_retorna_produtos_paginados()
    {
        Product::factory(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/product');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'links']);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_index_sem_permissao_retorna_403()
    {
        $coletor = User::factory()->create(['position' => 'COLETOR']);
        $token = $coletor->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/product')
            ->assertForbidden();
    }

    public function test_store_cria_produto()
    {
        $response = $this->withToken($this->token)->postJson('/api/product', [
            'code' => '12345',
            'description' => 'Produto Teste',
            'custo' => '15,50',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => 'Produto cadastrado com sucesso.']);
        $this->assertDatabaseHas('products', ['code' => '12345']);
    }

    public function test_show_retorna_produto()
    {
        $product = Product::factory()->create();

        $response = $this->withToken($this->token)->getJson("/api/product/{$product->id}");

        $response->assertOk();
        $response->assertJson(['id' => $product->id]);
    }

    public function test_update_produto()
    {
        $product = Product::factory()->create();

        $response = $this->withToken($this->token)->putJson("/api/product/{$product->id}", [
            'code' => $product->code,
            'description' => 'Descrição Atualizada',
            'custo' => 25.00,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'description' => 'Descrição Atualizada']);
    }

    public function test_destroy_produto()
    {
        $product = Product::factory()->create();

        $response = $this->withToken($this->token)->deleteJson("/api/product/{$product->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_findByCode_com_code()
    {
        $product = Product::factory()->create(['code' => '99999']);

        $response = $this->withToken($this->token)->getJson('/api/product-by-code/99999');

        $response->assertOk();
        $response->assertJson(['code' => '99999']);
    }

    public function test_findByCode_sem_code_retorna_400()
    {
        $response = $this->withToken($this->token)->getJson('/api/product-by-code');

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Código é obrigatório.']);
    }

    public function test_findByCode_inexistente_retorna_404()
    {
        $response = $this->withToken($this->token)->getJson('/api/product-by-code/00000');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Produto nao encontrado']);
    }

    public function test_saveAll_cria_multiplos_produtos()
    {
        $response = $this->withToken($this->token)->postJson('/api/product-save-all', [
            ['code' => '1001', 'description' => 'Produto A'],
            ['code' => '1002', 'description' => 'Produto B'],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('products', 2);
    }
}
