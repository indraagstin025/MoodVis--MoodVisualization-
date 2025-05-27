<?php

namespace Tests;

use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthControllerTest extends TestCase
{
    use DatabaseMigrations; // Supaya migrasi otomatis saat testing

    public function testRegister()
    {
        $response = $this->post('/register', [
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123'
        ]);

        $response->seeStatusCode(201);
        $response->seeJsonContains([
            'status' => 'success',
            'message' => 'User registered successfully. Please log in.'
        ]);
    }

    public function testLogin()
    {
        // Buat user dulu supaya bisa login
        User::create([
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => app('hash')->make('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'secret123'
        ]);

        $response->seeStatusCode(200);
        $response->seeJsonContains(['status' => 'success', 'message' => 'Login berhasil']);
        $response->seeJsonStructure(['token', 'user']);
    }

    public function testLogout()
    {
        // Buat user dan login untuk dapat token
        $user = User::create([
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => app('hash')->make('secret123'),
        ]);

        $token = auth()->login($user);

        $response = $this->post('/logout', [], ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(200);
        $response->seeJsonContains(['status' => 'success', 'message' => 'Successfully logged out']);
    }

    public function testMe()
    {
        // Buat user dan login untuk dapat token
        $user = User::create([
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => app('hash')->make('secret123'),
        ]);

        $token = auth()->login($user);

        $response = $this->get('/me', ['Authorization' => "Bearer $token"]);

        $response->seeStatusCode(200);
        $response->seeJsonContains([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
        ]);
    }

    public function testRoot()
{
    $response = $this->get('/');
    $response->assertResponseStatus(200);
}

}
