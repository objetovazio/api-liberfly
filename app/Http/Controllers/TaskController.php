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
     *     path="/api/todo/{todo_id}/tasks",
     *     summary="Get all tasks for a specific todo list",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="todo_id",
     *         in="path",
     *         description="ID of the todo list",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="List of tasks"),
     *     @OA\Response(response="400", description="Validation errors"),
     *     @OA\Response(response="500", description="Server error"),
     *     security={{"bearerAuth": {}}}
     * )
     * Display a listing of the resource.
     *
     * @param string $todo_id The ID of the todo list to fetch tasks from.
     * @return JsonResponse The response containing the list of tasks or an error message.
     */
    public function index(string $todo_id)
    {
        $userId = request()->user()->id;

        // Validação do ID para verificar se existe e se pertence ao usuário logado
        $validator = Validator::make(['id' => $todo_id, 'user_id' => $userId], [
            'id' => 'required|exists:todo_lists,id,user_id,' . $userId,
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors());
        }

        $tasks = $this->taskService->getAllByTodoListId($todo_id);
        return ApiResponse::success($tasks, "Tasks listed successfully");
    }

    /**
     * @OA\Post(
     *     path="/api/todo/{todo_id}/tasks",
     *     summary="Create a new task for a specific todo list",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="todo_id",
     *         in="path",
     *         description="ID of the todo list",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="My Task"),
     *             @OA\Property(property="description", type="string", example="Task description"),
     *         )
     *     ),
     *     @OA\Response(response="201", description="Task created successfully"),
     *     @OA\Response(response="400", description="Validation errors"),
     *     @OA\Response(response="500", description="Server error"),
     *     security={{"bearerAuth": {}}}
     * )
     * Store a newly created resource in storage.
     *
     * @param Request $request The request object containing task data.
     * @param string $todo_id The ID of the todo list to associate with the new task.
     * @return JsonResponse The response containing the created task or an error message.
     */
    public function store(Request $request, $todo_id)
    {
        $userId = request()->user()->id;

        // Extrai todos os dados do request e adiciona o todo_id
        $data = $request->all();
        $data['todo_list_id'] = $todo_id;

        // Validação dos dados
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'todo_list_id' => 'required|exists:todo_lists,id,user_id,' . $userId,
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->toJson(), 400);
        }

        $task = $this->taskService->create($data);
        return ApiResponse::success($task, 'Task created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/todo/{todo_id}/tasks/{task_id}",
     *     summary="Get a task by ID for a specific todo list",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="todo_id",
     *         in="path",
     *         description="ID of the todo list",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="ID of the task",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Task details"),
     *     @OA\Response(response="404", description="Task not found"),
     *     @OA\Response(response="500", description="Server error"),
     *     security={{"bearerAuth": {}}}
     * )
     * Display the specified resource.
     *
     * @param string $todoId The ID of the todo list to which the task belongs.
     * @param string $taskId The ID of the task to fetch.
     * @return JsonResponse The response containing the task details or an error message.
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
     *     path="/api/todo/{todo_id}/tasks/{task_id}",
     *     summary="Update a task for a specific todo list",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="todo_id",
     *         in="path",
     *         description="ID of the todo list",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="ID of the task",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Updated Task"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="completed", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response="200", description="Task updated successfully"),
     *     @OA\Response(response="400", description="Validation errors"),
     *     @OA\Response(response="404", description="Task not found"),
     *     @OA\Response(response="500", description="Server error"),
     *     security={{"bearerAuth": {}}}
     * )
     * Update the specified resource in storage.
     *
     * @param Request $request The request object containing updated task data.
     * @param string $todoId The ID of the todo list to which the task belongs.
     * @param string $taskId The ID of the task to update.
     * @return JsonResponse The response containing the updated task or an error message.
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

    /**
     * @OA\Delete(
     *     path="/api/todo/{todo_id}/tasks/{task_id}",
     *     summary="Delete a task for a specific todo list",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="todo_id",
     *         in="path",
     *         description="ID of the todo list",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         description="ID of the task",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="204", description="Task deleted successfully"),
     *     @OA\Response(response="404", description="Task not found"),
     *     @OA\Response(response="500", description="Server error"),
     *     security={{"bearerAuth": {}}}
     * )
     * Remove the specified resource from storage.
     *
     * @param string $todoId The ID of the todo list to which the task belongs.
     * @param string $taskId The ID of the task to delete.
     * @return JsonResponse The response indicating the result of the deletion.
     */
    public function destroy(string $todoId, string $taskId)
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

        // Deleta a tarefa
        $this->taskService->delete($taskId);
        return response()->json(null, 204);
    }
}
