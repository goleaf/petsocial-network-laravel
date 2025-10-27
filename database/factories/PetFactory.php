<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pet>
 */
class PetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Pet::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->firstName(),
            'type' => $this->faker->randomElement(['dog', 'cat', 'bird', 'rabbit']),
            'breed' => $this->faker->word(),
            'birthdate' => $this->faker->date(),
            'avatar' => null,
        ];
    }
}
