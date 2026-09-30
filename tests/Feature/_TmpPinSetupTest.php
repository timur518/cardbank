<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class _TmpPinSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_set_pin_first_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'pin' => '1234',
            'pin_confirmation' => '1234',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.has_pin', true);
        $this->assertTrue($user->fresh()->hasPin());
    }

    public function test_requires_current_pin_when_already_set(): void
    {
        $user = User::factory()->create(['pin_hash' => bcrypt('1111')]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'pin' => '2222',
            'pin_confirmation' => '2222',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('current_pin');
    }

    public function test_rejects_wrong_current_pin(): void
    {
        $user = User::factory()->create(['pin_hash' => bcrypt('1111')]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'current_pin' => '9999',
            'pin' => '2222',
            'pin_confirmation' => '2222',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('current_pin');
    }

    public function test_can_change_pin_with_correct_current(): void
    {
        $user = User::factory()->create(['pin_hash' => bcrypt('1111')]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'current_pin' => '1111',
            'pin' => '2222',
            'pin_confirmation' => '2222',
        ]);

        $response->assertOk();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('2222', $user->fresh()->pin_hash));
    }

    public function test_rejects_mismatched_confirmation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'pin' => '1234',
            'pin_confirmation' => '4321',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('pin');
    }
}
