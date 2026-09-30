<?php

namespace Tests\Feature;

use App\Models\PinDeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Такие запросы в реальности всегда приходят с Origin/Referer (SPA), от которых зависит,
 * запустит ли Laravel EncryptCookies/AddQueuedCookiesToResponse/StartSession для api-группы
 * (см. Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::fromFrontend()) —
 * без этого заголовка в тестах кука mojno_pin_device не попадёт в ответ и $request->session()
 * упадёт с "Session store not set on request", поэтому withHeader('Origin', ...) обязателен.
 */
class _TmpPinDeviceTest extends TestCase
{
    use RefreshDatabase;

    private function fromFrontend(): static
    {
        // withCredentials() — без него json()/getJson()/postJson() не отправляют куки вообще
        // (см. MakesHttpRequests::prepareCookiesForJsonRequest()) — точный аналог withCredentials: true у axios на фронте.
        return $this->withHeader('Origin', 'http://localhost')->withCredentials();
    }

    public function test_device_status_reports_untrusted_without_cookie(): void
    {
        $response = $this->fromFrontend()->getJson('/api/v1/auth/device-status');
        $response->assertOk()->assertJson(['trusted' => false]);
    }

    public function test_full_pin_device_flow(): void
    {
        $user = User::factory()->create(['email' => 'ivan@example.com']);

        $setResponse = $this->fromFrontend()->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'pin' => '4321',
            'pin_confirmation' => '4321',
        ]);
        $setResponse->assertOk();

        $cookie = $setResponse->getCookie(PinDeviceToken::COOKIE_NAME);
        $this->assertNotNull($cookie, 'Кука mojno_pin_device не была выставлена');
        $this->assertDatabaseCount('pin_device_tokens', 1);

        // device-status без активной сессии, но с кукой доверенного устройства.
        $statusResponse = $this->fromFrontend()
            ->withCookie(PinDeviceToken::COOKIE_NAME, $cookie->getValue())
            ->getJson('/api/v1/auth/device-status');
        $statusResponse->assertOk();
        $statusResponse->assertJson(['trusted' => true]);
        $this->assertSame('i***@example.com', $statusResponse->json('masked_email'));

        // Успешная разблокировка по ПИН-коду без сессии.
        $unlockResponse = $this->fromFrontend()
            ->withCookie(PinDeviceToken::COOKIE_NAME, $cookie->getValue())
            ->postJson('/api/v1/auth/unlock-pin', ['pin' => '4321']);
        $unlockResponse->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_unlock_pin_fails_with_wrong_pin(): void
    {
        $user = User::factory()->create();

        $setResponse = $this->fromFrontend()->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'pin' => '4321',
            'pin_confirmation' => '4321',
        ]);
        $cookie = $setResponse->getCookie(PinDeviceToken::COOKIE_NAME);

        $response = $this->fromFrontend()
            ->withCookie(PinDeviceToken::COOKIE_NAME, $cookie->getValue())
            ->postJson('/api/v1/auth/unlock-pin', ['pin' => '9999']);

        $response->assertStatus(422);
    }

    public function test_unlock_pin_revokes_device_after_max_failed_attempts(): void
    {
        $user = User::factory()->create();

        $setResponse = $this->fromFrontend()->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'pin' => '4321',
            'pin_confirmation' => '4321',
        ]);
        $cookie = $setResponse->getCookie(PinDeviceToken::COOKIE_NAME);
        $value = $cookie->getValue();

        for ($i = 0; $i < 4; $i++) {
            $this->fromFrontend()
                ->withCookie(PinDeviceToken::COOKIE_NAME, $value)
                ->postJson('/api/v1/auth/unlock-pin', ['pin' => '9999'])
                ->assertStatus(422);
        }

        // 5-я подряд неверная попытка — отзыв доверия устройству.
        $this->fromFrontend()
            ->withCookie(PinDeviceToken::COOKIE_NAME, $value)
            ->postJson('/api/v1/auth/unlock-pin', ['pin' => '9999'])
            ->assertStatus(401);

        $this->assertDatabaseCount('pin_device_tokens', 0);
    }

    public function test_logout_revokes_device_trust(): void
    {
        $user = User::factory()->create();

        $setResponse = $this->fromFrontend()->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'pin' => '4321',
            'pin_confirmation' => '4321',
        ]);
        $cookie = $setResponse->getCookie(PinDeviceToken::COOKIE_NAME);

        // В браузере httpOnly-кука едет со всеми запросами автоматически; в тесте нужно прикрепить явно.
        $this->fromFrontend()
            ->withCookie(PinDeviceToken::COOKIE_NAME, $cookie->getValue())
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        $this->assertDatabaseCount('pin_device_tokens', 0);

        $statusResponse = $this->fromFrontend()
            ->withCookie(PinDeviceToken::COOKIE_NAME, $cookie->getValue())
            ->getJson('/api/v1/auth/device-status');
        $statusResponse->assertJson(['trusted' => false]);
    }

    public function test_forget_device_endpoint_revokes_trust(): void
    {
        $user = User::factory()->create();

        $setResponse = $this->fromFrontend()->actingAs($user, 'sanctum')->postJson('/api/v1/profile/pin', [
            'pin' => '4321',
            'pin_confirmation' => '4321',
        ]);
        $cookie = $setResponse->getCookie(PinDeviceToken::COOKIE_NAME);

        $this->fromFrontend()
            ->withCookie(PinDeviceToken::COOKIE_NAME, $cookie->getValue())
            ->postJson('/api/v1/auth/forget-device')
            ->assertNoContent();

        $this->assertDatabaseCount('pin_device_tokens', 0);
    }
}
