<?php

namespace Tests\Feature;

use App\Models\Coleta;
use App\Models\Loja;
use App\Models\RecolhimentoRegra;
use App\Models\Product;
use App\Models\Barcode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecolhimentoApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Loja $loja;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['position' => 'ADMIN']);
        $this->loja = Loja::factory()->create();

        // Cria regra para hoje
        RecolhimentoRegra::factory()->create([
            'dia_semana' => now()->dayOfWeek,
            'dias_antecedencia' => 5,
        ]);
    }

    public function test_lista_produtos_para_recolher()
    {
        $product = Product::factory()->create(['custo' => 10.50]);
        Barcode::factory()->create([
            'ean' => '1234567890123',
            'product_id' => $product->id,
        ]);

        Coleta::factory()->create([
            'loja_id' => $this->loja->id,
            'ean' => '1234567890123',
            'descricao' => 'Produto Teste',
            'quantidade' => 10,
            'data_validade' => now()->addDays(3),
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/recolhimento/produtos/{$this->loja->id}");

        $response->assertOk();
        $response->assertJsonStructure(['produtos', 'total']);
        $this->assertCount(1, $response->json('produtos'));
    }

    public function test_registrar_recolhimento_com_quantidade_positiva()
    {
        $coleta = Coleta::factory()->create([
            'loja_id' => $this->loja->id,
            'data_validade' => now()->addDays(3),
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/recolhimento/registrar', [
                'coleta_id' => $coleta->id,
                'quantidade' => 5,
            ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Recolhimento registrado com sucesso.']);

        $coleta->refresh();
        $this->assertNotNull($coleta->recolhido_em);
        $this->assertEquals(5, (float) $coleta->recolhido_quantidade);
    }

    public function test_registrar_recolhimento_com_quantidade_zero_soft_delete()
    {
        $coleta = Coleta::factory()->create([
            'loja_id' => $this->loja->id,
            'data_validade' => now()->addDays(3),
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/recolhimento/registrar', [
                'coleta_id' => $coleta->id,
                'quantidade' => 0,
            ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Item removido (quantidade zero).']);

        $this->assertSoftDeleted($coleta);
    }

    public function test_lista_vazia_sem_regra_para_hoje()
    {
        RecolhimentoRegra::truncate();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/recolhimento/produtos/{$this->loja->id}");

        $response->assertOk();
        $this->assertEquals(0, $response->json('total'));
    }
}
