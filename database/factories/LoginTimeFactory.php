<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoginTime>
 */
class LoginTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $loginTime = $this->faker->dateTimeBetween('-1 week', 'now'); // Random time within the past week
        $logoutTime = (clone $loginTime)->add(new \DateInterval('PT' . rand(1, 8) . 'H')); // Random logout time between 1 to 8 hours after login

        return [
            'user_id' => User::inRandomOrder()->first()->id, // Generate a new User if not provided
            'login_time' => $loginTime->format('Y-m-d H:i:s'), // Format as a string for the database
            'logout_time' => $logoutTime->format('Y-m-d H:i:s'), // Format as a string for the database
        ];
    }
}
