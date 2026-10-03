<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class _TmpReferralInviteCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_gets_unique_invite_code_on_creation(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->invite_code);
        $this->assertSame(6, strlen($user->invite_code));
        $this->assertSame(strtoupper($user->invite_code), $user->invite_code);
    }

    public function test_invite_codes_are_unique_across_users(): void
    {
        $users = User::factory()->count(20)->create();

        $this->assertSame(20, $users->pluck('invite_code')->unique()->count());
    }

    public function test_register_stores_referral_code_from_pid_cookie_value(): void
    {
        $referrer = User::factory()->create(['invite_code' => 'X71KJN']);

        $payload = [
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'phone' => '+79990000002',
            'email' => 'referred-user@example.com',
            'date_of_birth' => now()->subYears(25)->format('d.m.Y'),
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'personal_data_consent' => true,
            // Фронтенд подставляет сюда значение cookie mojno_pid (см. tracking.ts).
            'referral_code' => $referrer->invite_code,
        ];

        $response = $this->withHeader('Origin', 'http://localhost')
            ->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201);

        $newUser = User::where('email', 'referred-user@example.com')->firstOrFail();

        $this->assertSame('X71KJN', $newUser->referral_code);
        $this->assertNotNull($newUser->invite_code);
        $this->assertNotSame($referrer->invite_code, $newUser->invite_code);
    }
}
