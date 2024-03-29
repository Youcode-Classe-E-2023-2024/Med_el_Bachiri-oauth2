<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected array $user = ['name', 'email', 'password', 'access_token'];

    public function setUp(): void
    {
        parent::setUp();

        $name = fake()->userName;

        $this->user['name'] = $name;
        $this->user['email'] = $name . '@gmail.com';
        $this->user['password'] = $name . '@gmail.com';
        $this->user['access_token'] = '';
    }

    public function test_registration_returns_a_successful_response()
    {
        $response = $this->postJson(
            '/api/auth/register',
            [
                'name' => $this->user['name'],
                'email' => $this->user['email'],
                'password' => $this->user['password']
            ]
        );

        $response->assertStatus(201);
    }

    public function test_login_process_is_successful_and_returns_user_access_token()
    {
        $this->test_registration_returns_a_successful_response(); // register before testing

        $response = $this->postJson(
            '/api/auth/login',
            [
                'email' => $this->user['email'],
                'password' => $this->user['password'],
            ]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                    'access_token'
                ]
            ]);

        $this->user['access_token'] = $response->json('user.access_token');
    }

    public function test_logout_process_is_successful()
    {
        $this->test_login_process_is_successful_and_returns_user_access_token(); // login before testing
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->user['access_token'],
        ])->postJson('/api/logout');

        $response->assertStatus(200);
    }
}
