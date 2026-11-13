<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    public static function malformedCredentials(): array
    {
        return [
            'missing credentials' => [[], ['username', 'password']],
            'missing username' => [['password' => 'password'], ['username']],
            'missing password' => [['username' => 'someone'], ['password']],
            'empty credentials' => [['username' => '', 'password' => ''], ['username', 'password']],
            'array username' => [['username' => ['someone'], 'password' => 'password'], ['username']],
            'array password' => [['username' => 'someone', 'password' => ['password']], ['password']],
            'long username' => [['username' => str_repeat('x', 129), 'password' => 'password'], ['username']],
        ];
    }

    #[DataProvider('malformedCredentials')]
    public function test_malformed_json_credentials_return_field_errors(array $credentials, array $fields): void
    {
        $this->postJson('/login', $credentials)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($fields);

        $this->assertGuest();
    }

    #[DataProvider('malformedCredentials')]
    public function test_malformed_form_credentials_redirect_with_field_errors(array $credentials, array $fields): void
    {
        $this->from('/login')->post('/login', $credentials)
            ->assertRedirect('/login')
            ->assertSessionHasErrors($fields);

        $this->assertGuest();
    }

    public function test_wrong_credentials_keep_existing_error_response(): void
    {
        User::factory()->create(['name' => 'someone']);

        $this->from('/login')->post('/login', ['username' => 'someone', 'password' => 'incorrect'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['username' => 'The provided credentials do not match our records.']);

        $this->assertGuest();
    }

    #[DataProvider('loginRoles')]
    public function test_valid_credentials_authenticate_and_redirect(string $role, string $destination): void
    {
        $user = User::factory()->create(['name' => 'someone', 'user_type' => $role]);

        $this->post('/login', ['username' => 'someone', 'password' => 'password'])
            ->assertRedirect($destination);

        $this->assertAuthenticatedAs($user);
    }

    public static function loginRoles(): array
    {
        return [
            'admin' => ['admin', '/admin/dashboard'],
            'candidate' => ['user', '/code-problems'],
        ];
    }
}
