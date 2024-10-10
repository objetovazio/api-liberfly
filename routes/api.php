<?php

use App\Http\Controllers\AuthController;
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
        Route::get('/', [TodoListController::class, 'index']); // Listar todas as listas de tarefas
        Route::post('/', [TodoListController::class, 'store']); // Criar uma nova lista de tarefas
        Route::get('/{id}', [TodoListController::class, 'show']); // Mostrar uma lista de tarefas específica
        Route::put('/{id}', [TodoListController::class, 'update']); // Atualizar uma lista de tarefas
        Route::delete('/{id}', [TodoListController::class, 'destroy']); // Deletar uma lista de tarefas
    });

    Route::get('/test', [AuthController::class, 'test']);
});
