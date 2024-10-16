<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/api/auth/create",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="User's name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="User's email",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="User's password",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password_confirmation",
     *         in="query",
     *         description="User's password confirmation",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="201", description="User registered successfully"),
     *     @OA\Response(response="400", description="Validation errors")
     * )
     */
    public function create(): JsonResponse
    {
        // Validação dos dados de registro do usuário.
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        // Se a validação falhar, retorna os erros de validação.
        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        // Chama o serviço para criar o usuário.
        $user = $this->authService->create(request()->all());
        return ApiResponse::success($user, 'User registered successfully', 201);
    }

    /**
     * @OA\Post(
     *     path="/api/auth",
     *     summary="Authenticate user and get token",
     *     tags={"Authentication"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="User's email",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="User's password",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="User authenticated successfully"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function login(): JsonResponse
    {
        // Recupera as credenciais do usuário.
        $credentials = request(['email', 'password']);

        // Tenta autenticar o usuário.
        if (!$token = auth('api')->attempt($credentials)) {
            return ApiResponse::error('Unauthorized', 401);
        }

        // Retorna o token de acesso.
        return $this->respondWithToken($token);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout the authenticated user",
     *     tags={"Authentication"},
     *     @OA\Response(response="200", description="Successfully logged out"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function logout(): JsonResponse
    {
        // Logout do usuário autenticado.
        auth('api')->logout();
        return ApiResponse::success(null, 'Successfully logged out');
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh the token",
     *     tags={"Authentication"},
     *     @OA\Response(response="200", description="Token refreshed successfully"),
     *     @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function refresh(): JsonResponse
    {
        // Obtém o token atual e gera um novo.
        $token = JWTAuth::getToken();
        $new_token = JWTAuth::refresh($token);

        // Retorna o novo token de acesso.
        return $this->respondWithToken($new_token);
    }

    protected function respondWithToken($token): JsonResponse
    {
        // Retorna a resposta com o token de acesso.
        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ], "Logged Successfully");
    }

    /**
     * @OA\Get(
     *     path="/api/auth/user",
     *     summary="Get logged-in user details",
     *     tags={"Authentication"},
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function getUserDetails(): JsonResponse
    {
        // Recupera os detalhes do usuário autenticado.
        $user = request()->user();

        return ApiResponse::success($user, 'User details retrieved successfully');
    }
}
