<?php

namespace App\Services;

use App\Repositories\TaskRepository;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Retorna todas as tasks
     */
    public function getAll()
    {
        return $this->taskRepository->getAll();
    }

    /**
     * Cria uma nova task
     */
    public function create(array $data)
    {
        $data =  $this->taskRepository->create($data);
        return $data;
    }

    /**
     * Retorna uma task por ID
     */
    public function getById(string $id)
    {
        return $this->taskRepository->getById($id);
    }

    /**
     * Retorna todas as tasks de uma lista específica (todoListId)
     */
    public function getAllByTodoListId($todoListId)
    {
        return $this->taskRepository->getAllByTodoListId($todoListId);
    }

    /**
     * Atualiza uma task por ID
     */
    public function update(string $id, array $data)
    {
        return $this->taskRepository->update($id, $data);
    }

    /**
     * Deleta uma task por ID
     */
    public function delete(string $id)
    {
        return $this->taskRepository->delete($id);
    }
}
