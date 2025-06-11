<?php

namespace Database\Factories;

use App\Models\Admin;

use Illuminate\Support\Facades\Hash;



use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('testpass'),
        ];
    }
}
