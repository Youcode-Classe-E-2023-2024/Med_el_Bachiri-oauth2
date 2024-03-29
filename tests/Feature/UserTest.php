<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_getting_user_details(): void
    {
        $user = User::factory()->create();
        $auth = new AuthController();
        $request = new Request([
            'email' => $user->email,
            'password' => $user->email,
        ]);
        $login_response = $auth->login($request);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $login_response->original['user']['access_token'],
        ])->getJson('/api/@me');

        $response->assertStatus(200);
    }

    public function test_getting_all_users()
    {
        $user = User::factory()->create();
        $userToDelete = User::factory()->create();

        $auth = new AuthController();
        $request = new Request([
            'email' => $user->email,
            'password' => $user->email,
        ]);
        $login_response = $auth->login($request);
        $accessToken = $login_response->original['user']['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->deleteJson('/api/user/delete/' . $userToDelete->id);

        $response->assertStatus(200);
    }

}
