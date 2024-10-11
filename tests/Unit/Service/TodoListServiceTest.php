<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\TodoList;
use App\Repositories\TodoListRepository;
use App\Services\TodoListService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TodoListServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TodoListRepository $todoListRepository;
    protected TodoListService $todoListService;

    protected function setUp(): void
    {
        parent::setUp();

        // Criação do mock do repositório com Mockery
        $this->todoListRepository = Mockery::mock(TodoListRepository::class);
        // Injeção do mock no serviço
        $this->todoListService = new TodoListService($this->todoListRepository);
    }

    public function testCanCreateTodoList()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        $todoListData = [
            'name' => 'Lista de Tarefas de Teste',
            'user_id' => $user->id,
        ];

        // Define o comportamento do mock para o método create
        $this->todoListRepository
            ->shouldReceive('create')
            ->once()
            ->with($todoListData)
            ->andReturn(new TodoList($todoListData)); // Retorna a instância criada

        // Cria a lista de tarefas usando o serviço
        $todoList = $this->todoListService->create($todoListData);

        // Verifica se a lista de tarefas foi criada corretamente
        $this->assertInstanceOf(TodoList::class, $todoList);
        $this->assertEquals($todoListData['name'], $todoList->name);
        $this->assertEquals($user->id, $todoList->user_id); // Verifica a associação com o usuário
    }

    public function testCanGetAllTodoListsByUser()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria listas de tarefas de teste
        $todoLists = TodoList::factory(2)->create(['user_id' => $user->id])->toArray();

        // Definindo o comportamento do mock - o método getAllByUserId retornará as listas criadas
        $this->todoListRepository
            ->shouldReceive('getAllByUserId')
            ->once()
            ->with($user->id) // Espera que o método seja chamado com o ID do usuário
            ->andReturn($todoLists); // Retorna as listas de tarefas

        // Obtém todas as listas de tarefas
        $todoListsFromService = $this->todoListService->getAllByUserId($user->id);

        // Verifica se todas as listas foram retornadas
        $this->assertCount(2, $todoListsFromService);
    }
    
    public function testCanGetTodoListById()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas de teste
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Definindo o comportamento do mock - o método find retornará a lista criada
        $this->todoListRepository
            ->shouldReceive('find')
            ->once()
            ->with($todoList->id) // Espera que o método seja chamado com o ID da lista
            ->andReturn($todoList); // Retorna a lista de tarefas

        // Obtém a lista de tarefas pelo ID
        $fetchedTodoList = $this->todoListService->getById($todoList->id);

        // Verifica se a lista de tarefas retornada é a mesma que a criada
        $this->assertEquals($todoList->id, $fetchedTodoList->id);
    }

    public function testGetTodoListByIdThrowsException()
    {
        // Espera que a exceção seja lançada
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Define o comportamento do mock para o método find
        $this->todoListRepository
            ->shouldReceive('find')
            ->once()
            ->with(999) // ID que não existe
            ->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException());

        // Tenta buscar uma lista de tarefas com um ID que não existe
        $this->todoListService->getById(999);
    }

    public function testCanUpdateTodoList()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas de teste
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        $updatedData = [
            'name' => 'Lista Atualizada',
        ];

        // Define o comportamento do mock para o método update
        $this->todoListRepository
            ->shouldReceive('update')
            ->once()
            ->with($todoList->id, $updatedData)
            ->andReturn(new TodoList(array_merge($todoList->toArray(), $updatedData))); // Retorna a instância atualizada

        // Atualiza a lista de tarefas
        $updatedTodoList = $this->todoListService->update($todoList->id, $updatedData);

        // Verifica se a lista de tarefas foi atualizada corretamente
        $this->assertEquals($updatedData['name'], $updatedTodoList->name);
    }

    public function testCanDeleteTodoList()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas de teste
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Define o comportamento do mock para o método delete
        $this->todoListRepository
            ->shouldReceive('delete')
            ->once()
            ->with($todoList->id)
            ->andReturn($todoList); // Retorna a lista de tarefas que foi deletada

        // Deleta a lista de tarefas usando o serviço
        $deletedTodoList = $this->todoListService->delete($todoList->id);

        // Verifica se a lista de tarefas foi deletada
        $this->assertEquals($todoList->id, $deletedTodoList->id);

        // Define o comportamento do mock - o método find lançará uma exceção se chamado
        $this->todoListRepository
            ->shouldReceive('find')
            ->once()
            ->with($todoList->id) // Espera que o método seja chamado com o ID da lista
            ->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException()); // Se o método for chamado, lança uma exceção

        // Verifica que a lista de tarefas não pode ser encontrada
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Tenta buscar a lista que foi deletada
        $this->todoListService->getById($todoList->id); // Deve lançar a exceção
    }
}
