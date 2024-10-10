<?php

namespace App\Services;

use App\Repositories\AuthRepository;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    // Método para criar um novo usuário
    public function create(array $data)
    {
        // Criptografa a senha antes de salvar
        $data['password'] = bcrypt($data['password']);
        return $this->authRepository->create($data);
    }
}
