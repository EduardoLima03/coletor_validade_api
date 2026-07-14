<?php

namespace Tests\Feature;

use App\Models\RecolhimentoRegra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecolhimentoRegraTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['position' => 'ADMIN']);
    }

    public function test_admin_pode_listar_regras()
    {
        RecolhimentoRegra::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.recolhimento-regras.index'));

        $response->assertOk();
        $response->assertViewHas('regras');
    }

    public function test_admin_pode_criar_regra()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.recolhimento-regras.store'), [
                'dia_semana' => 1,
                'dias_antecedencia' => 2,
                'ativo' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('recolhimento_regras', [
            'dia_semana' => 1,
            'dias_antecedencia' => 2,
            'ativo' => true,
        ]);
    }

    public function test_admin_pode_atualizar_regra()
    {
        $regra = RecolhimentoRegra::factory()->create([
            'dia_semana' => 1,
            'dias_antecedencia' => 2,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.recolhimento-regras.update', $regra), [
                'dia_semana' => 5,
                'dias_antecedencia' => 3,
                'ativo' => false,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('recolhimento_regras', [
            'id' => $regra->id,
            'dia_semana' => 5,
            'dias_antecedencia' => 3,
            'ativo' => false,
        ]);
    }

    public function test_admin_pode_excluir_regra()
    {
        $regra = RecolhimentoRegra::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.recolhimento-regras.destroy', $regra));

        $response->assertRedirect();
        $this->assertModelMissing($regra);
    }

    public function test_usuario_nao_admin_nao_pode_gerenciar_regras()
    {
        $user = User::factory()->create(['position' => 'COLETOR']);

        $response = $this->actingAs($user)
            ->get(route('admin.recolhimento-regras.index'));

        $response->assertForbidden();
    }

    public function test_dias_antecedencia_para_hoje_sem_regra()
    {
        // Nenhuma regra cadastrada para hoje
        $dias = RecolhimentoRegra::diasAntecedenciaParaHoje();
        $this->assertNull($dias);
    }

    public function test_dias_antecedencia_para_hoje_com_regra()
    {
        $hoje = now()->dayOfWeek;
        RecolhimentoRegra::factory()->create([
            'dia_semana' => $hoje,
            'dias_antecedencia' => 3,
            'ativo' => true,
        ]);

        $dias = RecolhimentoRegra::diasAntecedenciaParaHoje();
        $this->assertEquals(3, $dias);
    }
}
