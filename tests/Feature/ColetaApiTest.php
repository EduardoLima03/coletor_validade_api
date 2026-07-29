<?php

namespace Tests\Feature;

use App\Models\AreaAuditoria;
use App\Models\Barcode;
use App\Models\Coleta;
use App\Models\Loja;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColetaApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['position' => 'COLETOR']);
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_store_cria_coleta()
    {
        $loja = Loja::factory()->create();

        $response = $this->withToken($this->token)->postJson('/api/coleta', [
            'loja_id' => $loja->id,
            'ean' => '1234567890123',
            'quantidade' => 10,
            'unidade' => 'un',
            'validade' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'ean' => '1234567890123',
            'quantidade' => '10.00',
        ]);
        $this->assertDatabaseHas('coletas', ['ean' => '1234567890123']);
    }

    public function test_store_com_action_replace()
    {
        $loja = Loja::factory()->create();
        $product = Product::factory()->create();
        Barcode::factory()->create(['ean' => '1234567890123', 'product_id' => $product->id]);

        Coleta::factory()->create([
            'loja_id' => $loja->id,
            'ean' => '1234567890123',
            'quantidade' => 5,
            'data_validade' => now()->addDays(10),
        ]);

        $response = $this->withToken($this->token)->postJson('/api/coleta', [
            'loja_id' => $loja->id,
            'ean' => '1234567890123',
            'quantidade' => 15,
            'validade' => now()->addDays(10)->format('Y-m-d'),
            'action' => 'replace',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('coletas', ['ean' => '1234567890123', 'quantidade' => '15.00']);
    }

    public function test_store_sem_action_retorna_409_duplicado()
    {
        $loja = Loja::factory()->create();

        $this->withToken($this->token)->postJson('/api/coleta', [
            'loja_id' => $loja->id,
            'ean' => '1234567890123',
            'quantidade' => 10,
            'validade' => now()->addDays(10)->format('Y-m-d'),
        ])->assertStatus(201);

        $response = $this->withToken($this->token)->postJson('/api/coleta', [
            'loja_id' => $loja->id,
            'ean' => '1234567890123',
            'quantidade' => 10,
            'validade' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $response->assertStatus(409);
        $response->assertJsonStructure(['message', 'existing']);
    }

    public function test_update_coleta()
    {
        $loja = Loja::factory()->create();
        $coleta = Coleta::factory()->create(['loja_id' => $loja->id]);

        $response = $this->withToken($this->token)->putJson("/api/coleta/{$coleta->id}", [
            'quantidade' => 20,
            'validade' => now()->addDays(15)->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('coletas', ['id' => $coleta->id, 'quantidade' => '20.00']);
    }

    public function test_update_com_quantidade_zero_soft_delete()
    {
        $loja = Loja::factory()->create();
        $coleta = Coleta::factory()->create(['loja_id' => $loja->id]);

        $response = $this->withToken($this->token)->putJson("/api/coleta/{$coleta->id}", [
            'quantidade' => 0,
            'validade' => now()->addDays(15)->format('Y-m-d'),
        ]);

        $response->assertStatus(200);
        $this->assertSoftDeleted($coleta);
    }

    public function test_check_retorna_exists_true()
    {
        $loja = Loja::factory()->create();
        $coleta = Coleta::factory()->create(['loja_id' => $loja->id]);

        $response = $this->withToken($this->token)->getJson('/api/coleta/check?' . http_build_query([
            'loja_id' => $coleta->loja_id,
            'ean' => $coleta->ean,
            'validade' => $coleta->data_validade->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertJson(['exists' => true]);
    }

    public function test_check_retorna_exists_false()
    {
        $loja = Loja::factory()->create();

        $response = $this->withToken($this->token)->getJson('/api/coleta/check?' . http_build_query([
            'loja_id' => $loja->id,
            'ean' => '0000000000000',
            'validade' => now()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertJson(['exists' => false]);
    }

    public function test_trashed_sem_permissao_retorna_403()
    {
        $this->withToken($this->token)->getJson('/api/coleta/trashed')
            ->assertForbidden();
    }

    public function test_trashed_com_permissao()
    {
        $gerente = User::factory()->create(['position' => 'GERENCIA']);
        $token = $gerente->createToken('test')->plainTextToken;

        $coleta = Coleta::factory()->create();
        $coleta->delete();

        $response = $this->withToken($token)->getJson('/api/coleta/trashed');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_restore()
    {
        $gerente = User::factory()->create(['position' => 'GERENCIA']);
        $token = $gerente->createToken('test')->plainTextToken;

        $coleta = Coleta::factory()->create();
        $coleta->delete();

        $this->withToken($token)->putJson("/api/coleta/{$coleta->id}/restore")
            ->assertOk();

        $this->assertNotSoftDeleted($coleta);
    }
}
