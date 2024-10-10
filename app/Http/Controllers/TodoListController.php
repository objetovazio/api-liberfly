<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\TodoListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TodoListController extends Controller
{
    protected $todoListService;

    public function __construct(TodoListService $todoListService)
    {
        $this->todoListService = $todoListService;
    }

    /**
     * @OA\Get(
     *     path="/api/todo",
     *     summary="Get all todo lists",
     *     tags={"Todo Lists"},
     *     @OA\Response(response="200", description="Success"),
     * )
     */
    public function index(): JsonResponse
    {
        // Carrega o ID do usuário ao request logado
        $userId = request()->user()->id;

        $todoLists = $this->todoListService->getAllByUserId($userId);
        return ApiResponse::success($todoLists, "TodoList listed Sucessfully");
    }

    /**
     * @OA\Post(
     *     path="/api/todo",
     *     summary="Create a new todo list",
     *     tags={"Todo Lists"},
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Title of the todo list",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="201", description="Todo list created successfully"),
     *     @OA\Response(response="400", description="Validation errors")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:todo_lists,name,NULL,id,user_id,' . $userId,
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        // Adicionar o ID do usuário ao request
        $requestData = array_merge($request->all(), ['user_id' => $request->user()->id]);

        $todoList = $this->todoListService->create($requestData);
        return ApiResponse::success($todoList, 'Todo list created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/todo/{id}",
     *     summary="Get a todo list by ID",
     *     tags={"Todo Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="404", description="Todo list not found")
     * )
     */
    public function show(string $id): JsonResponse
    {
        $userId = request()->user()->id;

        // Validação do ID para verificar se existe
        $validator = Validator::make(
            ['id' => $id],
            ['id' => 'required|exists:todo_lists,id,user_id,' . $userId]
        );

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()); // Retorna erro se a validação falhar
        }

        $todoList = $this->todoListService->getById($id);
        return ApiResponse::success($todoList);
    }

    /**
     * @OA\Put(
     *     path="/api/todo/{id}",
     *     summary="Update a todo list",
     *     tags={"Todo Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Title of the todo list",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Todo list updated successfully"),
     *     @OA\Response(response="404", description="Todo list not found"),
     *     @OA\Response(response="400", description="Validation errors")
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:todo_lists,name,user_id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        $todoList = $this->todoListService->update($id, $request->all());
        return ApiResponse::success($todoList, 'Todo list updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/todo/{id}",
     *     summary="Delete a todo list",
     *     tags={"Todo Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Todo list deleted successfully"),
     *     @OA\Response(response="404", description="Todo list not found")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        // Validação do ID para verificar se existe
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:todo_lists,id', // Valida se o ID existe na tabela todo_lists
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()); // Retorna erro se a validação falhar
        }

        // Chama o serviço para deletar a TodoList
        $this->todoListService->delete($id);
        return ApiResponse::success(null, 'Todo list deleted successfully', 204);
    }
}
