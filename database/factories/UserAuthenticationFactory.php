<?php

namespace Database\Factories;

use App\Models\UserAuthentication;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserAuthenticationFactory extends Factory
{
    protected $model = UserAuthentication::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // You can adjust this as needed
            'phone_no' => $this->faker->phoneNumber,
            // Other attributes
        ];
    }
}
