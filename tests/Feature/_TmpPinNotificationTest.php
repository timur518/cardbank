<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class _TmpPinNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_pin_set_on_first_time(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'pin' => '1234',
            'pin_confirmation' => '1234',
        ])->assertOk();

        $notification = Notification::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($notification);
        $this->assertSame('Установлен ПИН-код', $notification->title);
    }

    public function test_notifies_pin_changed_when_already_set(): void
    {
        $user = User::factory()->create(['pin_hash' => bcrypt('1111')]);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'current_pin' => '1111',
            'pin' => '2222',
            'pin_confirmation' => '2222',
        ])->assertOk();

        $notification = Notification::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($notification);
        $this->assertSame('ПИН-код изменён', $notification->title);
    }
}
