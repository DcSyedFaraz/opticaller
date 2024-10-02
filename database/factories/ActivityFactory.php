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
        $address = Address::inRandomOrder()->first();
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'address_id' => $address->id,
            'contact_id' => $address->contact_id,
            'feedback' => $address->feedback,
            'activity_type' => $this->faker->randomElement(['call', 'break']),

            'total_duration' => $this->faker->numberBetween(1, 3600),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
