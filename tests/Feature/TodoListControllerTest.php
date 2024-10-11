<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TodoListControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateUser()
    {
        // Cria um usuário e gera um token JWT
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $token = JWTAuth::fromUser($user);

        return ['user' => $user, 'token' => $token];
    }

    public function testUserCanAccessTodoLists()
    {
        $credentials = $this->authenticateUser();

        // Chama a rota com o token JWT
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->getJson('/api/todo');

        // Verifica se o status é 200 (OK)
        $response->assertStatus(200);
    }

    public function testIndexReturnsAllTodoLists()
    {
        $credentials = $this->authenticateUser();
        $user = $credentials['user'];

        // Cria algumas listas de tarefas associadas ao usuário
        $todoLists = \App\Models\TodoList::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->getJson('/api/todo');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => $todoLists->toArray(),
        ]);
        $response->assertJsonStructure(['data', 'message']);
    }

    public function testStoreCreatesTodoListSuccessfully()
    {
        $credentials = $this->authenticateUser();
        $user = $credentials['user'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->postJson('/api/todo', ['name' => 'New List']);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Todo list created successfully',
            'data' => [
                'name' => 'New List',
            ],
        ]);
        $this->assertDatabaseHas('todo_lists', ['name' => 'New List', 'user_id' => $user->id]); // Verifica se a lista foi criada no banco
    }

    public function testShowReturnsTodoList()
    {
        $credentials = $this->authenticateUser();
        $user = $credentials['user'];

        $todoList = \App\Models\TodoList::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->getJson('/api/todo/' . $todoList->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $todoList->id,
                'name' => $todoList->name,
            ],
        ]);
    }

    public function testUpdateUpdatesTodoListSuccessfully()
    {
        $credentials = $this->authenticateUser();
        $user = $credentials['user'];

        $todoList = \App\Models\TodoList::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->putJson('/api/todo/' . $todoList->id, ['name' => 'Updated List']);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Todo list updated successfully',
            'data' => [
                'name' => 'Updated List',
            ],
        ]);
        $this->assertDatabaseHas('todo_lists', ['id' => $todoList->id, 'name' => 'Updated List']); // Verifica se a lista foi atualizada no banco
    }

    public function testDestroyDeletesTodoListSuccessfully()
    {
        $credentials = $this->authenticateUser();
        $user = $credentials['user'];

        $todoList = \App\Models\TodoList::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->deleteJson('/api/todo/' . $todoList->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('todo_lists', ['id' => $todoList->id]); // Verifica se o registro foi removido
    }
}
