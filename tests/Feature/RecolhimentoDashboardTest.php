<?php

namespace Tests\Feature;

use App\Models\Coleta;
use App\Models\Loja;
use App\Models\Product;
use App\Models\Barcode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecolhimentoDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $gerente;
    private Loja $loja;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gerente = User::factory()->create(['position' => 'GERENCIA']);
        $this->loja = Loja::factory()->create();

        // Produto com custo
        $product = Product::factory()->create(['custo' => 25.00]);
        Barcode::factory()->create([
            'ean' => '9876543210987',
            'product_id' => $product->id,
        ]);

        // Coleta recolhida
        Coleta::factory()->create([
            'loja_id' => $this->loja->id,
            'ean' => '9876543210987',
            'descricao' => 'Produto Recolhido',
            'quantidade' => 10,
            'data_validade' => now()->addDays(5),
            'user_id' => $this->gerente->id,
            'recolhido_em' => now(),
            'recolhido_quantidade' => 8,
            'recolhido_user_id' => $this->gerente->id,
        ]);
    }

    public function test_gerente_pode_ver_dashboard()
    {
        $response = $this->actingAs($this->gerente)
            ->get(route('admin.recolhimento.dashboard'));

        $response->assertOk();
        $response->assertViewHasAll([
            'totalRegistros', 'totalQuantidade', 'totalValor',
            'produtosDistintos', 'porLoja', 'itens', 'comparativoMensal',
        ]);
    }

    public function test_dashboard_mostra_valor_calculado()
    {
        $response = $this->actingAs($this->gerente)
            ->get(route('admin.recolhimento.dashboard'));

        $response->assertOk();
        $response->assertSee('R$ 200,00'); // 25 * 8 = 200
    }

    public function test_dashboard_filtra_por_loja()
    {
        $outraLoja = Loja::factory()->create();
        Coleta::factory()->create([
            'loja_id' => $outraLoja->id,
            'recolhido_em' => now(),
            'recolhido_quantidade' => 5,
            'user_id' => $this->gerente->id,
        ]);

        $response = $this->actingAs($this->gerente)
            ->get(route('admin.recolhimento.dashboard', ['loja_id' => $this->loja->id]));

        $response->assertOk();
        $this->assertEquals(1, $response->viewData('totalRegistros'));
    }
}
