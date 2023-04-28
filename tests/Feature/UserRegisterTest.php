<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserRegisterTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_can_access_the_register_page()
    {
        $response = $this->postJson('api/register');

        // dd($response->json());
        $response->assertStatus(422);
    }

}
