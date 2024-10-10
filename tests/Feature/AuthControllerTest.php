<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testUserCanRegister()
    {
        $response = $this->postJson('/api/auth/create', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Verifique se o status da resposta é 201
        $response->assertStatus(201);

        // Verifique se a resposta contém os dados do usuário
        $response->assertJsonFragment([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Verifique se o usuário foi adicionado ao banco de dados
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        // Verifique se a senha do usuário está criptografada
        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotEquals('password123', $user->password);
    }

    public function testUserCannotRegisterWithInvalidData()
    {
        $response = $this->postJson('/api/auth/create', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'notmatching',
        ]);

        // Verifique se a resposta contém erros de validação
        $response
            ->assertStatus(400)
            ->assertJsonStructure([
                'name',
                'email',
                'password',
            ], json_decode($response->getContent(), true));
    }

    public function testUserCanLogin()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->postJson('/api/auth', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);

        // Extraindo o token da resposta
        $token = $response->json('access_token');

        // Verificar se o token JWT pode ser usado é valido em uma rota protegida
        $authenticatedResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/user');

        $authenticatedResponse->assertStatus(200)
            ->assertJsonFragment([
                'email' => $user->email,
            ]);
    }

    public function testUserCannotLoginWithInvalidCredentials()
    {
        $response = $this->postJson('/api/auth', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
    }

    public function testUserCanLogout()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Successfully logged out']);
    }

    public function testUserCanRefreshToken()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $token = JWTAuth::fromUser($user);

        // Call the refresh token endpoint
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    public function testGetUserDetails()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $token = JWTAuth::fromUser($user);

        // Call the user details endpoint
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    }
}
