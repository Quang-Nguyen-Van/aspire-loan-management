<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRegisterTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_can_register_successfully()
    {
        $response = $this->postJson('api/register', [
            'name' => 'abcd',
            'email' => 'abcd@example.com',
            'password' => '1234abcd',
            'password_confirmation' => '1234abcd',
        ])
        ->assertCreated(201)
        ->assertJson([
            'message' => 'User had been created',
        ]);
    }



    /** @dataProvider invalidUserInformation */
    public function test_user_information_register_validation(array $data, string $key)
    {
        if($key == "email"){
            User::factory()->create(['email' => 'not-unique@gmail.com']);
        }

        $response = $this->postJson('api/register', $data);

        $response->assertInvalid($key, 'StoreUserRequest');
    }

    /* create dataprovider */
    public function invalidUserInformation(): array
    {
        return [
            'name.required' => [['name' => ''], 'name'],
            'name.string' => [['name' => 123], 'name'],
            'name.max' => [['name' => Str::random(256)], 'name'],

            'email.required' => [['email' => ''], 'email'],
            'email.string' => [['email' => 123], 'email'],
            'email.email' => [['email' => 'invalid.email'], 'email'],
            'email.max' => [['email' => Str::random(255) . '@gmail.com'], 'email'],
            'email.unique' => [['email' => 'not-unique@gmail.com'], 'email'],
        ];
    }



    public function test_if_password_of_user_is_empty_then_raises_error()
    {
        $response = $this->postJson('api/register', [
            'name' => 'abcd',
            'email' => 'abcd@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertUnprocessable()->assertJson([
                    'message' => 'The password field is required.',
                ]);
    }


    public function test_if_password_confirmation_of_user_is_empty_then_raises_error()
    {
        $response = $this->postJson('api/register', [
            'name' => 'abcd',
            'email' => 'abcd@example.com',
            'password' => '1234abcd',
            'password_confirmation' => '',
        ]);

        $response->assertUnprocessable()->assertJson([
                    'message' => 'The password confirmation does not match.',
                ]);
    }

}
