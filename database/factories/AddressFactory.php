<?php

namespace Database\Factories;

use App\Models\SubProject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
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
            'company_name' => $this->faker->company,
            'salutation' => $this->faker->title,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'street_address' => $this->faker->streetAddress,
            'postal_code' => $this->faker->postcode,
            'city' => $this->faker->city,
            'country' => $this->faker->country,
            'website' => $this->faker->url,
            'phone_number' => $this->faker->phoneNumber,
            'email_address_system' => fake()->unique()->safeEmail(),
            'email_address_new' => $this->faker->safeEmail,
            // 'priority' => $this->faker->numberBetween(0, 4),
            'sub_project_id' => SubProject::inRandomOrder()->first()->id,
            // 'personal_notes' => $this->faker->paragraph,
            // 'feedback' => 'Follow-up',
            'feedback' => $this->faker->randomElement(['Not Interested', 'Interested', 'Request', 'Follow-up', 'Delete Address']),
            // 'follow_up_date' => '2024-08-26 15:23:08',
            'created_at' => $createdAt, // Set created_at to a random day of the current week
            'updated_at' => $createdAt,
        ];
    }
}
