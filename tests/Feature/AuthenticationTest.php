<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = $this->makeUser('admin');

        $this->post('/login', ['email' => $user->email, 'password' => 'salah-total'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_correct_credentials_can_login(): void
    {
        $user = $this->makeUser('admin');

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_staff_login_lands_on_products_not_dashboard(): void
    {
        $user = $this->makeUser('staff');

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/products');
    }
}
