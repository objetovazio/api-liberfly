<?php

namespace Tests\Unit\Service;

use Tests\TestCase;
use App\Services\TaskService;
use App\Models\Task;
use App\Models\TodoList;
use App\Models\User; // Importa a model de User
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskRepository $taskRepository;
    protected TaskService $taskService;


    protected function setUp(): void
    {
        parent::setUp();

        // Criação do mock do repositório com Mockery
        $this->taskRepository = Mockery::mock(TaskRepository::class);
        // Injeção do mock no serviço
        $this->taskService = new TaskService($this->taskRepository);
    }

    public function testCanCreateTask()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas de teste
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Dados de teste para a tarefa
        $taskData = [
            'title' => 'Tarefa de Teste',
            'completed' => false,
            'todo_list_id' => $todoList->id, // Usa o ID da lista de tarefas criada
        ];

        // Define o comportamento do mock para o método create
        $this->taskRepository
            ->shouldReceive('create')
            ->once()
            ->with($taskData)
            ->andReturn(new Task($taskData)); // Retorna a instância criada

        // Cria a tarefa usando o serviço
        $task = $this->taskService->create($taskData);

        // Verifica se a tarefa foi criada corretamente
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($taskData['title'], $task->title);
        $this->assertFalse($task->completed);
        $this->assertEquals($todoList->id, $task->todo_list_id); // Verifica a associação
    }

    public function testCanGetAllTasks()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas de teste
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Cria duas tarefas de teste
        $tasks = Task::factory(2)->create(['todo_list_id' => $todoList->id]);

        // Define o comportamento do mock para o método create
        $this->taskRepository
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($tasks); // Retorna a instância criada

        // Recupera todas as tarefas usando o serviço
        $tasks = $this->taskService->getAll();

        // Verifica se duas tarefas foram recuperadas
        $this->assertCount(2, $tasks);
    }

    public function testCanGetTaskById()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas de teste
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Cria uma tarefa de teste
        $task = Task::factory()->create(['todo_list_id' => $todoList->id]);

        // Define o comportamento do mock para o método create
        $this->taskRepository
            ->shouldReceive('getById')
            ->once()
            ->with($task->id)
            ->andReturn($task); // Retorna a instância criada

        // Recupera a tarefa usando o serviço
        $foundTask = $this->taskService->getById($task->id);

        // Verifica se a tarefa encontrada é a mesma que foi criada
        $this->assertEquals($task->id, $foundTask->id);
    }

    public function testCanUpdateTask()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas de teste
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Cria uma tarefa de teste
        $task = Task::factory()->create(['todo_list_id' => $todoList->id]);

        $updatedData = [
            'title' => 'Tarefa Atualizada',
            'completed' => true,
        ];

        // Cria uma nova instância de Task com os dados atualizados
        $updatedTask = $task->replicate(); // Faz uma cópia da tarefa original
        $updatedTask->fill($updatedData); // Preenche os dados atualizados
        $updatedTask->save(); // Salva a tarefa atualizada

        // Define o comportamento do mock para o método update
        $this->taskRepository
            ->shouldReceive('update')
            ->once()
            ->with($task->id, $updatedData)
            ->andReturn($updatedTask); // Retorna a instância atualizada

        // Atualiza a tarefa usando o serviço
        $result = $this->taskService->update($task->id, $updatedData);

        // Verifica se a tarefa foi atualizada corretamente
        $this->assertEquals($updatedTask->title, $result->title);
        $this->assertTrue($result->completed);
    }

    public function testCanDeleteTask()
    {
        // Cria um usuário de teste
        $user = User::factory()->create();

        // Cria uma lista de tarefas de teste
        $todoList = TodoList::factory()->create(['user_id' => $user->id]);

        // Cria uma tarefa de teste
        $task = Task::factory()->create(['todo_list_id' => $todoList->id]);

        // Define o comportamento do mock para o método update
        $this->taskRepository
            ->shouldReceive('delete')
            ->once()
            ->with($task->id)
            ->andReturn($task); // Retorna a instância atualizada

        // Deleta a tarefa usando o serviço
        $deletedTask = $this->taskService->delete($task->id);

        // Verifica se a tarefa foi deletada
        $this->assertEquals($task->id, $deletedTask->id);
    }
}
