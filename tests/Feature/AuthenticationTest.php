<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $credentials = [
            'name'     => '::name::',
            'email'    => 'test@example.com',
            'password' => '::password::',
            'password_confirmation' => '::password::'
        ];

        $response = $this->postJson('/api/register', $credentials);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('token')
                     ->has('user', fn (AssertableJson $json) =>
                        $json->where('name', $credentials['name'])
                             ->where('email', $credentials['email'])
                )
            );
    }

    public function test_user_can_not_register_when_failing_password_confirmation(): void
    {
        $credentials = [
            'name'     => '::name::',
            'email'    => 'test@example.com',
            'password' => '::password::',
            'password_confirmation' => '::wrong_password::'
        ];

        $response = $this->postJson('/api/register', $credentials);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('password', 'errors');
    }

    public function test_user_can_not_register_when_using_existing_email(): void
    {
        $oldUser = User::factory()->create();

        $credentials = [
            'name'     => '::name::',
            'email'    => $oldUser->email,
            'password' => '::password::',
            'password_confirmation' => '::password::'
        ];

        $response = $this->postJson('/api/register', $credentials);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('email', 'errors');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('token')
                     ->has('user', fn (AssertableJson $json) =>
                        $json->where('name', $user->name)
                             ->where('email', $user->email)
                )
            );
    }

    public function test_user_can_not_login_with_incorrect_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_logout(): void
    {
        $this->logInWithAbilities(['none']);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
    }
}
