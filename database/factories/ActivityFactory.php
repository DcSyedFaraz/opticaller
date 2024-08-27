<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = Carbon::now()->startOfWeek()->addDays(rand(0, 6));
        return [
            'user_id' => User::inRandomOrder()->first()->id, // Generate a new User if not provided
            'address_id' => Address::inRandomOrder()->first()->id, // Generate a new Address if not provided
            // 'project_id' => Project::inRandomOrder()->first()->id->nullable(), // Uncomment and use if a Project model exists
            'activity_type' => $this->faker->randomElement(['call', 'break']), // Example of different activity types
            // 'starting_time' => $this->faker->time('H:i:s'), // Random start time
            // 'ending_time' => $this->faker->time('H:i:s'), // Random end time
            'total_duration' => $this->faker->numberBetween(1, 3600), // Random duration in seconds
            'created_at' => $createdAt, // Set created_at to a random day of the current week
            'updated_at' => $createdAt,
        ];
    }
}
