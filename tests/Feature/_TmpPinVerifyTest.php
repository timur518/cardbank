<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class _TmpPinVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_succeeds_with_correct_pin(): void
    {
        $user = User::factory()->create(['pin_hash' => bcrypt('1234')]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin/verify', [
            'pin' => '1234',
        ]);

        $response->assertOk();
        $response->assertJson(['valid' => true]);
    }

    public function test_verify_fails_with_wrong_pin(): void
    {
        $user = User::factory()->create(['pin_hash' => bcrypt('1234')]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin/verify', [
            'pin' => '9999',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('pin');
    }

    public function test_verify_fails_when_no_pin_set(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin/verify', [
            'pin' => '1234',
        ]);

        $response->assertStatus(422);
    }
}
