<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_updates_user_password_after_valid_code(): void
    {
        $user = User::query()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'old-password',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => 'demo@example.com',
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'demo@example.com',
            'code' => '123456',
            'password' => 'new-password',
        ]);

        $response->assertOk()->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email'],
        ]);

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'demo@example.com',
        ]);
    }
}
