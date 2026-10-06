<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\NotificationRead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class _TmpBroadcastNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_broadcast_notification_is_visible_to_all_users(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $broadcast = Notification::notifyAll('promo', 'Всем привет', 'Текст рассылки');

        $this->assertNull($broadcast->user_id);

        foreach ([$userA, $userB] as $user) {
            $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/notifications')->assertOk();
            $ids = collect($response->json('data'))->pluck('id');
            $this->assertTrue($ids->contains($broadcast->uuid));
        }
    }

    public function test_personal_notification_is_not_visible_to_other_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $personal = Notification::notify($owner, \App\Enums\NotificationEvent::PinSet);

        $response = $this->actingAs($other, 'sanctum')->getJson('/api/v1/notifications')->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse($ids->contains($personal->uuid));

        $response = $this->actingAs($owner, 'sanctum')->getJson('/api/v1/notifications')->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($personal->uuid));
    }

    public function test_reading_broadcast_notification_by_one_user_does_not_affect_others(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $broadcast = Notification::notifyAll('promo', 'Всем привет');

        $this->actingAs($userA, 'sanctum')->postJson('/api/v1/notifications/mark-read')->assertOk();

        $this->assertDatabaseHas('notification_reads', [
            'notification_id' => $broadcast->id,
            'user_id' => $userA->id,
        ]);
        $this->assertDatabaseMissing('notification_reads', [
            'notification_id' => $broadcast->id,
            'user_id' => $userB->id,
        ]);

        // userA видит его прочитанным, userB — всё ещё непрочитанным.
        $responseA = $this->actingAs($userA, 'sanctum')->getJson('/api/v1/notifications')->assertOk();
        $itemA = collect($responseA->json('data'))->firstWhere('id', $broadcast->uuid);
        $this->assertTrue($itemA['is_read']);

        $responseB = $this->actingAs($userB, 'sanctum')->getJson('/api/v1/notifications')->assertOk();
        $itemB = collect($responseB->json('data'))->firstWhere('id', $broadcast->uuid);
        $this->assertFalse($itemB['is_read']);
        $this->assertSame(1, $responseB->json('meta.unread_count'));
    }

    public function test_mark_as_read_by_is_idempotent_for_broadcast(): void
    {
        $user = User::factory()->create();
        $broadcast = Notification::notifyAll('promo', 'Всем привет');

        $broadcast->markAsReadBy($user->id);
        $broadcast->markAsReadBy($user->id);

        $this->assertSame(1, NotificationRead::where('notification_id', $broadcast->id)->where('user_id', $user->id)->count());
    }
}
