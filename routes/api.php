<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoListController;


Route::group(['middleware' => 'api'], function ($router) {
    Route::group(['prefix' => 'auth'], function ($router) {
        Route::post('/', [AuthController::class, 'login'])->name('login');
        Route::post('/create', [AuthController::class, 'create'])->name('create');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
        Route::get('/user', [AuthController::class, 'getUserDetails'])->middleware('auth:api')->name('getUserDetails');
    });


    // Rotas para TodoLists

    Route::group(['prefix' => 'todo', 'middleware' => 'auth:api'], function () {
        // Rotas para TodoList
        Route::get('/', [TodoListController::class, 'index']); // Listar todas as listas
        Route::post('/', [TodoListController::class, 'store']); // Criar uma nova lista
        Route::get('/{id}', [TodoListController::class, 'show']); // Mostrar uma lista específica
        Route::put('/{id}', [TodoListController::class, 'update']); // Atualizar uma lista
        Route::delete('/{id}', [TodoListController::class, 'destroy']); // Deletar uma lista

        // Rotas para Tasks
        Route::group(['prefix' => '{todo_id}/tasks'], function () {
            Route::get('/', [TaskController::class, 'index']); // Listar todas as tarefas da lista
            Route::post('/', [TaskController::class, 'store']); // Criar uma nova tarefa na lista
            Route::get('/{task_id}', [TaskController::class, 'show']); // Mostrar uma tarefa específica
            Route::put('/{task_id}', [TaskController::class, 'update']); // Atualizar uma tarefa
            Route::delete('/{task_id}', [TaskController::class, 'destroy']); // Deletar uma tarefa
        });
    });;
});
