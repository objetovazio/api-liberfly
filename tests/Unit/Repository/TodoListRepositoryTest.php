<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Repositories\TodoListRepository;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TodoListRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $todoListRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->todoListRepository = new TodoListRepository();
    }

    public function testCanCreateTodoList()
    {
        // Cria um usuário de teste usando a factory
        $user = User::factory()->create();

        // Dados de teste para a lista de tarefas
        $todoListData = [
            'name' => 'Lista de Tarefas de Teste',
            'user_id' => $user->id, // Usa o ID do usuário criado pela factory
        ];

        // Cria uma lista de tarefas usando o repositório
        $todoList = $this->todoListRepository->create($todoListData);

        // Verifica se a lista de tarefas foi criada corretamente
        $this->assertInstanceOf(TodoList::class, $todoList);
        $this->assertEquals($todoListData['name'], $todoList->name);
        $this->assertEquals($todoListData['user_id'], $todoList->user_id);
    }

    public function testCanFindTodoList()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas usando a factory com um usuário
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Busca a lista de tarefas pelo ID
        $foundTodoList = $this->todoListRepository->find($todoList->id);

        // Verifica se a lista de tarefas encontrada é a mesma que foi criada
        $this->assertEquals($todoList->id, $foundTodoList->id);
    }

    public function testCanUpdateTodoList()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas usando a factory
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Dados para atualizar a lista de tarefas
        $updatedData = [
            'name' => 'Lista de Tarefas Atualizada',
        ];

        // Atualiza a lista de tarefas usando o repositório
        $updatedTodoList = $this->todoListRepository->update($todoList->id, $updatedData);

        // Verifica se a lista de tarefas foi atualizada corretamente
        $this->assertEquals($updatedData['name'], $updatedTodoList->name);
    }

    public function testCanDeleteTodoList()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas usando a factory
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Deleta a lista de tarefas usando o repositório
        $this->todoListRepository->delete($todoList->id);

        // Verifica se a lista de tarefas foi deletada
        $this->assertNull($this->todoListRepository->find($todoList->id));
    }

    public function testCanRetrieveAllTodoLists()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria duas listas de tarefas usando a factory com um usuário
        TodoList::factory()->create(['user_id' => $user->id, 'name' => 'Lista 1']);
        TodoList::factory()->create(['user_id' => $user->id, 'name' => 'Lista 2']);

        // Recupera todas as listas de tarefas usando o repositório
        $todoLists = $this->todoListRepository->all();

        // Verifica se duas listas de tarefas foram recuperadas
        $this->assertCount(2, $todoLists);
    }
}
