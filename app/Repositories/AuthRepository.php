<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    // Método para criar um novo usuário
    public function create(array $data)
    {
        return User::create($data);
    }

    // Método para encontrar um usuário pelo email
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }
}
