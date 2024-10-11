<?php

use App\Models\User;
use App\Models\TodoList;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticateUser()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $token = JWTAuth::fromUser($user);

        return ['user' => $user, 'token' => $token];
    }

    public function testUserCanAccessTasks()
    {
        $credentials = $this->authenticateUser();
        $todoList = TodoList::factory()->create(['user_id' => $credentials['user']->id]);

        // Chama a rota com o token JWT
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->getJson('/api/todo/' . $todoList->id . '/tasks'); // Atualizada

        // Verifica se o status é 200 (OK)
        $response->assertStatus(200);
    }

    public function testUserCanCreateTask()
    {
        $credentials = $this->authenticateUser();
        $todoList = TodoList::factory()->create(['user_id' => $credentials['user']->id]);

        $data = [
            'title' => 'New Task',
            'description' => 'Task description'
        ];

        // Chama a rota com o token JWT
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->postJson('/api/todo/' . $todoList->id . '/tasks', $data); // Atualizada

        // Verifica se a tarefa foi criada com sucesso
        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Task']);
    }

    public function testUserCanShowTask()
    {
        $credentials = $this->authenticateUser();
        $todoList = TodoList::factory()->create(['user_id' => $credentials['user']->id]);
        $task = Task::factory()->create(['todo_list_id' => $todoList->id]);

        // Chama a rota com o token JWT
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->getJson('/api/todo/' . $todoList->id . '/tasks/' . $task->id); // Atualizada

        // Verifica se a tarefa foi retornada com sucesso
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $task->title]);
    }

    public function testUserCanUpdateTask()
    {
        $credentials = $this->authenticateUser();
        $todoList = TodoList::factory()->create(['user_id' => $credentials['user']->id]);
        $task = Task::factory()->create(['todo_list_id' => $todoList->id]);

        $data = [
            'title' => 'Updated Task',
            'description' => 'Updated description',
            'completed' => true
        ];

        // Chama a rota com o token JWT
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->putJson('/api/todo/' . $todoList->id . '/tasks/' . $task->id, $data); // Atualizada

        // Verifica se a tarefa foi atualizada com sucesso
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Task']);
    }

    public function testUserCanDeleteTask()
    {
        $credentials = $this->authenticateUser();
        $todoList = TodoList::factory()->create(['user_id' => $credentials['user']->id]);
        $task = Task::factory()->create(['todo_list_id' => $todoList->id]);

        // Chama a rota com o token JWT
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $credentials['token']])
            ->deleteJson('/api/todo/' . $todoList->id . '/tasks/' . $task->id); // Atualizada

        // Verifica se a tarefa foi deletada com sucesso
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
