<?php

namespace Tests\Feature;

use App\Models\AreaAuditoria;
use App\Models\Loja;
use App\Models\Product;
use App\Models\RecolhimentoRegra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiTest extends TestCase
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

    public function test_lojas_retorna_lista_paginada()
    {
        Loja::factory(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/lojas');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_areas_auditoria_retorna_lista_paginada()
    {
        AreaAuditoria::factory(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/areas-auditoria');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    public function test_areas_auditoria_filtra_por_loja()
    {
        $loja = Loja::factory()->create();
        $area = AreaAuditoria::factory()->create();
        $area->lojas()->attach($loja);

        AreaAuditoria::factory(2)->create();

        $response = $this->withToken($this->token)->getJson('/api/areas-auditoria?loja_id=' . $loja->id);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_recolhimento_pendentes_retorna_dados()
    {
        RecolhimentoRegra::factory()->create(['dias_antecedencia' => 5]);
        Loja::factory()->create();

        $response = $this->withToken($this->token)->getJson('/api/recolhimento/pendentes/1');

        $response->assertOk();
        $response->assertJsonStructure(['total', 'produtos']);
    }

    public function test_user_update_com_admin()
    {
        $admin = User::factory()->create(['position' => 'ADMIN']);
        $token = $admin->createToken('test')->plainTextToken;

        $target = User::factory()->create();

        $response = $this->withToken($token)->postJson("/api/user-update/{$target->id}", [
            'name' => 'Nome Atualizado',
            'email' => $target->email,
            'position' => 'GERENCIA',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Nome Atualizado', 'position' => 'GERENCIA']);
    }

    public function test_user_update_sem_admin_retorna_403()
    {
        $coletor = User::factory()->create(['position' => 'COLETOR']);
        $token = $coletor->createToken('test')->plainTextToken;

        $target = User::factory()->create();

        $this->withToken($token)->postJson("/api/user-update/{$target->id}", [
            'name' => 'Hacker',
            'email' => $target->email,
            'position' => 'ADMIN',
        ])->assertForbidden();
    }

    public function test_recolhimento_produtos_sem_regra_retorna_lista_vazia()
    {
        Loja::factory()->create();

        $response = $this->withToken($this->token)->getJson('/api/recolhimento/produtos/1');

        $response->assertOk();
        $this->assertCount(0, $response->json('produtos'));
    }
}
