<?php

namespace Database\Factories;

use App\Models\User;
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
        return [
            'company_name' => $this->faker->company,
            'salutation' => $this->faker->title,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'street_address' => $this->faker->streetAddress,
            'postal_code' => $this->faker->postcode,
            'city' => $this->faker->city,
            'website' => $this->faker->url,
            'phone_number' => $this->faker->phoneNumber,
            'email_address_system' => fake()->unique()->safeEmail(),
            'email_address_new' => $this->faker->safeEmail,
            'priority' => $this->faker->numberBetween(0, 4),
            // 'personal_notes' => $this->faker->paragraph,
            'interest_notes' => $this->faker->paragraph,
            'feedback' => $this->faker->randomElement(['Not Interested', 'Interested', 'Request','Follow-up','Delete Address']),
            // 'follow_up_date' => $this->faker->date,
            'user_id' => 2,
        ];
    }
}
