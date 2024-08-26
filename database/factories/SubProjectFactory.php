<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubProject>
 */
class SubProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name,
            'description' => $this->faker->paragraph,
            'project_id' => Project::inRandomOrder()->first()->id,
        ];
    }
    public function configure()
    {
        return $this->afterCreating(function (SubProject $subProject) {
            // Assign a random User to each SubProject
            $users = User::orderBy('id', 'asc')->take(2)->pluck('id');
            $subProject->users()->attach($users);
        });
    }
}
