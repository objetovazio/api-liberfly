<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3), // Gera um título aleatório
            'completed' => $this->faker->boolean(), // Gera um valor booleano aleatório
            'todo_list_id' => \App\Models\TodoList::factory(), // Cria uma TodoList associada
        ];
    }
}
