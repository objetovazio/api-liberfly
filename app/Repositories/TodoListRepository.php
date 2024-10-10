<?php

namespace App\Repositories;

use App\Models\TodoList;

class TodoListRepository
{
    public function all()
    {
        return TodoList::all();
    }

    public function find($id)
    {
        return TodoList::find($id);
    }

    public function create(array $data)
    {
        return TodoList::create($data);
    }

    public function update($id, array $data)
    {
        $todoList = $this->find($id);
        $todoList->update($data);
        return $todoList;
    }

    public function delete($id)
    {
        return TodoList::destroy($id);
    }
}
