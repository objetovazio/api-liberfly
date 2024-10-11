<?php

namespace Tests\Unit\Repository;

use App\Models\Task;
use App\Models\TodoList;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Faker\Factory as Faker;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $taskRepository;
    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepository = new TaskRepository();

        // Cria uma lista de tarefas de teste
        TodoList::factory()->create([
            'name' => 'Test Todo List',
        ]);

        $this->faker = Faker::create();
    }

    public function testCanCreateTask()
    {
        // Cria uma lista de tarefas de teste
        $todoList = TodoList::factory()->create();

        $taskData = [
            'title' => $this->faker->sentence(3),
            'completed' => false,
            'todo_list_id' => $todoList->id,
        ];

        $task = $this->taskRepository->create($taskData);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($taskData['title'], $task->title);
        $this->assertFalse($task->completed);
        $this->assertEquals($todoList->id, $task->todo_list_id); // Verifica a associação
    }

    // Teste para verificar se todas as tarefas podem ser recuperadas
    public function testCanGetAllTasks()
    {
        // Cria 3 listas de tarefas e 3 tarefas associadas usando as factories
        TodoList::factory()->count(3)->create()->each(function ($todoList) {
            Task::factory()->count(1)->create(['todo_list_id' => $todoList->id]);
        });

        // Chama o método all do repositório para obter todas as tarefas
        $tasks = $this->taskRepository->getAll();

        // Verifica se o número de tarefas retornadas é igual a 3
        $this->assertCount(3, $tasks);
    }

    // Teste para verificar se uma tarefa específica pode ser encontrada
    public function testCanFindTask()
    {
        // Cria uma lista de tarefas e uma tarefa associada usando as factories
        $todoList = TodoList::factory()->create();
        $task = Task::factory()->create(['todo_list_id' => $todoList->id]);

        // Chama o método find do repositório para encontrar a tarefa
        $foundTask = $this->taskRepository->getById($task->id);

        // Verifica se a tarefa encontrada tem o mesmo ID da tarefa criada
        $this->assertEquals($task->id, $foundTask->id);
    }

    // Teste para verificar se uma tarefa pode ser atualizada
    public function testCanUpdateTask()
    {
        // Cria uma lista de tarefas e uma tarefa associada usando as factories
        $todoList = TodoList::factory()->create();
        $task = Task::factory()->create(['todo_list_id' => $todoList->id]);

        // Dados para atualizar a tarefa
        $data = [
            'title' => 'Título Atualizado', // Novo título
            'completed' => true, // Novo status de conclusão
        ];

        // Chama o método update do repositório para atualizar a tarefa
        $updatedTask = $this->taskRepository->update($task->id, $data);

        // Verifica se o título da tarefa foi atualizado corretamente
        $this->assertEquals('Título Atualizado', $updatedTask->title);
        // Verifica se o status de conclusão foi atualizado corretamente
        $this->assertTrue($updatedTask->completed);
    }

    // Teste para verificar se uma tarefa pode ser deletada
    public function testCanDeleteTask()
    {
        // Cria uma lista de tarefas e uma tarefa associada usando as factories
        $todoList = TodoList::factory()->create();
        $task = Task::factory()->create(['todo_list_id' => $todoList->id]);

        // Chama o método delete do repositório para deletar a tarefa
        $deleted = $this->taskRepository->delete($task->id);

        // Verifica se a tarefa foi deletada com sucesso
        $this->assertTrue(boolval($deleted));
        
        
         // Espera que a exceção seja lançada
         $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);$this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
         
        // Verifica se a tarefa não existe mais no banco de dados
        $this->assertNull($this->taskRepository->getById($task->id));
    }
}
