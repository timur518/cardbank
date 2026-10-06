<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class _TmpNotificationsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsSuperAdmin(): User
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin->assignRole($role);
        $this->actingAs($admin, 'web');

        return $admin;
    }

    public function test_notifications_list_page_is_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get('/admin/notifications')->assertOk();
    }

    public function test_notification_create_page_is_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get('/admin/notifications/create')->assertOk();
    }

    public function test_personal_notification_view_and_edit_pages_render(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create();
        $notification = $user->notifications()->create([
            'type' => 'system',
            'title' => 'Личное уведомление',
            'body' => 'Текст',
        ]);

        $this->get("/admin/notifications/{$notification->id}")->assertOk();
        $this->get("/admin/notifications/{$notification->id}/edit")->assertOk();
    }

    public function test_broadcast_notification_view_page_shows_reads_relation_manager(): void
    {
        $this->actingAsSuperAdmin();

        $broadcast = Notification::notifyAll('promo', 'Общая рассылка', 'Текст рассылки');

        $viewer = User::factory()->create();
        $broadcast->markAsReadBy($viewer->id);

        // Содержимое relation manager'а загружается lazy (x-intersect) и не попадает в
        // первый HTTP-ответ — проверяем, что сам componент зарегистрирован на странице
        // (т.е. canViewForRecord() вернул true) и отдельно что таблица возвращает нужные данные.
        $response = $this->get("/admin/notifications/{$broadcast->id}")->assertOk();
        $response->assertSee('reads-relation-manager', false);

        $this->assertTrue(\App\Filament\Admin\Resources\Notifications\RelationManagers\ReadsRelationManager::canViewForRecord($broadcast, \App\Filament\Admin\Resources\Notifications\Pages\ViewNotification::class));
        $this->assertTrue($broadcast->reads()->where('user_id', $viewer->id)->exists());
    }

    public function test_personal_notification_view_page_hides_reads_relation_manager(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create();
        $notification = $user->notifications()->create([
            'type' => 'system',
            'title' => 'Личное уведомление',
        ]);

        $response = $this->get("/admin/notifications/{$notification->id}")->assertOk();
        $response->assertDontSee('reads-relation-manager', false);

        $this->assertFalse(\App\Filament\Admin\Resources\Notifications\RelationManagers\ReadsRelationManager::canViewForRecord($notification, \App\Filament\Admin\Resources\Notifications\Pages\ViewNotification::class));
    }
}
