<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'teste@example.com',
            'password' => Hash::make($this->password),
            'position' => 'COLETOR',
        ]);
    }

    public function test_login_com_credenciais_validas_retorna_token()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'teste@example.com',
            'password' => $this->password,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'position']]);
        $this->assertNotNull($response->json('token'));
    }

    public function test_login_com_credenciais_invalidas_retorna_erro_json()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'teste@example.com',
            'password' => 'senha_errada',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Credenciais inválidas']);
    }

    public function test_login_sem_email_retorna_erro_validacao()
    {
        $response = $this->postJson('/api/login', [
            'password' => $this->password,
        ]);

        $response->assertStatus(422);
    }

    public function test_login_sem_password_retorna_erro_validacao()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'teste@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_revoga_tokens_anteriores()
    {
        $this->postJson('/api/login', [
            'email' => 'teste@example.com',
            'password' => $this->password,
        ])->assertOk();

        $this->postJson('/api/login', [
            'email' => 'teste@example.com',
            'password' => $this->password,
        ])->assertOk();

        $this->assertEquals(1, $this->user->tokens()->count());
    }

    public function test_rota_user_sem_auth_retorna_401()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_rota_user_com_auth_retorna_dados()
    {
        $token = $this->postJson('/api/login', [
            'email' => 'teste@example.com',
            'password' => $this->password,
        ])->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->getJson('/api/user');

        $response->assertOk();
        $response->assertJson([
            'id' => $this->user->id,
            'email' => 'teste@example.com',
        ]);
    }

    public function test_criar_usuario_sem_auth_retorna_401()
    {
        $response = $this->postJson('/api/user', [
            'name' => 'Novo Usuário',
            'email' => 'novo@example.com',
            'password' => '123456',
            'position' => 'COLETOR',
        ]);

        $response->assertStatus(401);
    }

    public function test_criar_usuario_com_admin_pode_criar()
    {
        $admin = User::factory()->create(['position' => 'ADMIN']);

        $token = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/user', [
                'name' => 'Novo Usuário',
                'email' => 'novo@example.com',
                'password' => '123456',
                'position' => 'COLETOR',
            ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => 'Usuário criado com sucesso.']);
        $this->assertDatabaseHas('users', ['email' => 'novo@example.com']);
    }

    public function test_criar_usuario_com_position_invalido_retorna_erro()
    {
        $admin = User::factory()->create(['position' => 'ADMIN']);

        $token = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/user', [
                'name' => 'Novo Usuário',
                'email' => 'novo2@example.com',
                'password' => '123456',
                'position' => 'INVALIDO',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['position']);
    }

    public function test_criar_usuario_nao_admin_nao_pode_criar()
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/user', [
                'name' => 'Novo Usuário',
                'email' => 'novo3@example.com',
                'password' => '123456',
                'position' => 'COLETOR',
            ]);

        $response->assertForbidden();
    }

    public function test_usuario_pode_atualizar_proprio_perfil()
    {
        $token = $this->postJson('/api/login', [
            'email' => 'teste@example.com',
            'password' => $this->password,
        ])->json('token');

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/profile/update', [
                'name' => 'Nome Atualizado',
                'password' => 'nova1234',
                'password_confirmation' => 'nova1234',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => 'Perfil atualizado com sucesso.']);
        $this->assertDatabaseHas('users', ['name' => 'Nome Atualizado']);
    }
}
