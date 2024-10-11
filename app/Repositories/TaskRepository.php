<?php

namespace App\Repositories;

use App\Models\Task;

class TaskRepository
{
    /**
     * Retorna todas as tasks
     */
    public function getAll()
    {
        return Task::all();
    }

    /**
     * Cria uma nova task
     */
    public function create(array $data)
    {
        $data = Task::create($data);
        return $data;
    }

    /**
     * Retorna uma task por ID
     */
    public function getById(string $id)
    {
        return Task::findOrFail($id);
    }

    /**
     * Retorna todas as tasks de uma lista específica (todoListId)
     */
    public function getAllByTodoListId($todoListId)
    {
        return Task::where('todo_list_id', $todoListId)->get();
    }

    /**
     * Atualiza uma task por ID
     */
    public function update(string $id, array $data)
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        return $task;
    }

    /**
     * Deleta uma task por ID
     */
    public function delete(string $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return $task;
    }
}
