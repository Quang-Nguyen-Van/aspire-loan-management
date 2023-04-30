<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_login_with_email_and_password()
    {
        $user = User::factory()->create();

        $response = $this->postJson('api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();


        $this->assertAuthenticated();
    }


    public function test_if_user_email_is_not_existing_then_it_return_error()
    {
        $this->postJson('/api/login', [
            'email'     => 'abc@example.com',
            'password'  => 'password',
        ])
        ->assertUnauthorized();
    }


    public function test_if_password_is_incorrect_then_it_raise_error()
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', [
            'email'     => $user->email,
            'password'  => 'abcd1234',
        ])
        ->assertUnauthorized();
    }
}
