<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email'    => 'staf@inventory.test',
            'password' => Hash::make($this->password),
        ]);
        
        // Setup role dummy (karena tabel roles mungkin kosong di testing)
        // Kita bypass pembuatan role permission yang kompleks untuk test sederhana ini
    }

    /**
     * Test Login Berhasil.
     */
    public function test_user_can_login_with_correct_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email'       => 'staf@inventory.test',
            'password'    => $this->password,
            'device_name' => 'Android',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'user', 'token']);
        
        $this->assertArrayHasKey('token', $response->json());
    }

    /**
     * Test Login Gagal.
     */
    public function test_user_cannot_login_with_wrong_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email'       => 'staf@inventory.test',
            'password'    => 'wrongpassword',
            'device_name' => 'Android',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test Endpoint Terproteksi.
     */
    public function test_protected_endpoints_require_token(): void
    {
        // Tanpa token
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);

        // Dengan token
        $token = $this->user->createToken('Test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/me');
        $response->assertStatus(200)
                 ->assertJsonFragment(['email' => $this->user->email]);
    }
}
