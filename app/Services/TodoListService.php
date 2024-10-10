<?php
namespace App\Services;

use App\Repositories\TodoListRepository;

class TodoListService
{
    protected $todoListRepository;

    public function __construct(TodoListRepository $todoListRepository)
    {
        $this->todoListRepository = $todoListRepository;
    }

    public function getAllByUserId(int $userId)
    {
        return $this->todoListRepository->getAllByUserId($userId);
    }

    public function create(array $data)
    {
        return $this->todoListRepository->create($data);
    }

    public function getById(string $id)
    {
        return $this->todoListRepository->find($id);
    }

    public function update(string $id, array $data)
    {
        return $this->todoListRepository->update($id, $data);
    }

    public function delete(string $id)
    {
        return $this->todoListRepository->delete($id);
    }
}
