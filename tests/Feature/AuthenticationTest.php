<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\UserAuthentication;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // This method runs before each test
    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database
        $this->seed();
    }

    public function testUserCanLoginWithValidCredentials()
    {
        // Create a UserAuthentication instance (you can customize this as needed)
        $user = UserAuthentication::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => bcrypt('password'),
        ]);

        // Attempt to log in with valid credentials
        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'password', // Use the actual password
        ]);

        // Assert that the login was successful (customize assertions as needed)
        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user']);
    }

    public function testUserCannotLoginWithInvalidCredentials()
    {
        // Attempt to log in with invalid credentials
        $response = $this->post('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'invalidpassword',
        ]);

        // Assert that the login failed
        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function testUserCanRegister()
{
    // Generate random user data for registration
    $userData = [
        'name' => $this->faker->name,
        'email' => $this->faker->unique()->safeEmail, // Ensure it's a unique email
        'password' => 'password', // Meet the minimum length requirement (adjust as needed)
        'phone_no' => '1234567890', // Provide a valid phone number (10 digits)
    ];

    // Attempt to register a new user
    $response = $this->post('/api/register', $userData);

    // Assert that the registration was successful (customize assertions as needed)
    $response->assertStatus(201)
        ->assertJson(['message' => 'User successfully registered']);
}


/**
     * Test user can logout.
     */
    public function testUserCanLogout()
    {
        // Create a user and log them in
        $user = UserAuthentication::factory()->create();
        $token = auth()->login($user);

        // Log out the authenticated user
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->post('/api/logout');

        // Assert that the logout was successful
        $response->assertStatus(200)
            ->assertJson(['message' => 'User successfully signed out']);
    }


        /**
     * Test user can refresh their token.
     */
    public function testUserCanRefreshToken()
    {
        // Create a user and log them in
        $user = UserAuthentication::factory()->create();
        $token = auth()->login($user);

        // Refresh the user's token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->post('/api/refresh');

        // Assert that the token was successfully refreshed
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }


    /**
     * Test getting the user's profile.
     */
    public function testGetUserProfile()
    {
        // Create a user and log them in
        $user = UserAuthentication::factory()->create();
        $token = auth()->login($user);

        // Request the user profile
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->get('/api/user-profile');

        // Assert that the user profile was retrieved successfully
        $response->assertStatus(200)
            ->assertJson($user->toArray());
    }



}
