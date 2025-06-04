<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'user_name' => $this->faker->unique()->userName,
            'first_name' => $this->faker->firstName,
            'middle_name' => $this->faker->lastName,
            'last_name' => $this->faker->lastName,
            'password' => Hash::make('password'),
            'phone' => $this->faker->unique()->phoneNumber,
            'location' => $this->faker->address,
            'role_id' => 4,
            'remember_token' => Str::random(10),
        ];
    }
}
