<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get all tasks",
     *     tags={"Tasks"},
     *     @OA\Response(response="200", description="List of tasks"),
     *     @OA\Response(response="500", description="Server error")
     * )
     * Display a listing of the resource.
     */
    public function index(string $todo_id)
    {
        $userId = request()->user()->id; // Obtém o ID do usuário logado

        // Validação do ID para verificar se existe e se pertence ao usuário logado
        $validator = Validator::make(['id' => $todo_id, 'user_id' => $userId], [
            'id' => 'required|exists:todo_lists,id,user_id,' . $userId, // Valida se o ID existe na tabela todo_lists e se pertence ao usuário logado
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()); // Retorna erro se a validação falhar
        }

        $tasks = $this->taskService->getAllByTodoListId($todo_id);
        return ApiResponse::success($tasks, "Tasks listed Sucessfully");
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     tags={"Tasks"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={""title"", "todo_list_id"},
     *             @OA\Property(property=""title"", type="string", example="My Task"),
     *             @OA\Property(property="description", type="string", example="Task description"),
     *             @OA\Property(property="todo_list_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response="201", description="Task created successfully"),
     *     @OA\Response(response="400", description="Validation errors"),
     *     @OA\Response(response="500", description="Server error")
     * )
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $todo_id)
    {
        $userId = request()->user()->id; // Obtém o ID do usuário logado

        // Extrai todos os dados do request e adiciona o todo_id
        $data = $request->all();
        $data['todo_list_id'] = $todo_id; // Adiciona o todo_id ao array de dados

        // Validação dos dados
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'todo_list_id' => 'required|exists:todo_lists,id,user_id,' . $userId, // Valida se o ID existe na tabela todo_lists e se pertence ao usuário logado
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->toJson(), 400);
        }

        $task = $this->taskService->create($data);
        return ApiResponse::success($task, 'Task created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Get a task by ID",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Task details"),
     *     @OA\Response(response="404", description="Task not found"),
     *     @OA\Response(response="500", description="Server error")
     * )
     * Display the specified resource.
     */
    public function show(string $todoId, string $taskId)
    {
        $userId = request()->user()->id;

        $data = [
            "id" => $taskId,
            "todo_list_id" => $todoId,
        ];

        // Validação dos dados
        $validator = Validator::make($data, [
            'id' => 'required|exists:tasks,id,todo_list_id,' . $todoId,
            'todo_list_id' => 'required|exists:todo_lists,id,user_id,' . $userId,
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $task = $this->taskService->getById($taskId);

        return ApiResponse::success($task, 'Task fetched successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Update a task",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={""title""},
     *             @OA\Property(property=""title"", type="string", example="Updated Task"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="completed", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response="200", description="Task updated successfully"),
     *     @OA\Response(response="400", description="Validation errors"),
     *     @OA\Response(response="404", description="Task not found"),
     *     @OA\Response(response="500", description="Server error")
     * )
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $todoId, string $taskId)
    {
        $userId = $request->user()->id;

        // Obtenha todos os dados da requisição
        $requestData = $request->all();

        $requestData['id'] = $taskId;
        $requestData['todo_list_id'] = $todoId;

        // Validação dos dados
        $validator = Validator::make($requestData, [
            'id' => 'required|exists:tasks,id,todo_list_id,' . $todoId,
            'todo_list_id' => 'required|exists:todo_lists,id,user_id,' . $userId,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->toJson(), 400);
        }

        // Atualiza a tarefa
        $task = $this->taskService->update($taskId, $requestData);
        return ApiResponse::success($task, 'Task updated successfully');
    }

    public function destroy(Request $request, string $todoId, string $taskId)
    {
        \Log::info('Attempting to delete task', ['todoId' => $todoId, 'taskId' => $taskId]);

        $userId = $request->user()->id; // Obtém o ID do usuário logado

        $requestData = [
            'id' => $taskId,
            'todo_list_id' => $todoId,
        ];

        // Validação dos dados
        $validator = Validator::make($requestData, [
            'id' => 'required|exists:tasks,id,todo_list_id,' . $todoId,
            'todo_list_id' => 'required|exists:todo_lists,id,user_id,' . $userId,
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->toJson(), 400);
        }

        // Deletar a tarefa usando o ID da tarefa
        $this->taskService->delete($taskId); // Corrigido para deletar a tarefa específica
        return ApiResponse::success(null, 'Task deleted successfully', 204);
    }
}
