<?php

namespace Database\Factories;

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TodoList>
 */
class TodoListFactory extends Factory
{
    protected $model = TodoList::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // Chama a factory de User para gerar o user_id
            'name' => $this->faker->sentence(3),
        ];
    }
}
