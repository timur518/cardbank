<?php

namespace Tests\Feature;

use App\Enums\CardsProCallbackType;
use App\Enums\CardStatus;
use App\Models\Card;
use App\Models\CardProduct;
use App\Models\CardProvider;
use App\Models\Setting;
use App\Models\User;
use App\Services\Integrations\CardsPro\CardsProWebhookHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CardsProStatusTelegramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['ok' => true])]);
        Setting::setMany([
            'notifications_bot_token' => 'test-bot-token',
            'notifications_notify_chat_id' => 'test-admin-chat',
        ]);
    }

    public static function statusEvents(): array
    {
        return [
            'block' => [CardsProCallbackType::CardBlock, CardStatus::Active, CardStatus::Closed, 'Карта заблокирована'],
            'freeze' => [CardsProCallbackType::CardFreeze, CardStatus::Active, CardStatus::Frozen, 'Карта заморожена'],
            'unfreeze' => [CardsProCallbackType::CardUnfreeze, CardStatus::Frozen, CardStatus::Active, 'Карта разморожена'],
        ];
    }

    #[DataProvider('statusEvents')]
    public function test_status_webhook_notifies_admin_once(CardsProCallbackType $type, CardStatus $oldStatus, CardStatus $newStatus, string $title): void
    {
        $card = $this->createCard($oldStatus);
        $handler = new CardsProWebhookHandler($card->provider);
        $payload = ['san' => $card->provider_card_id, 'status' => 'EXECUTED'];

        $handler->handle($type, $payload);
        $handler->handle($type, $payload);

        $this->assertSame($newStatus, $card->fresh()->status);
        $this->assertDatabaseCount('card_status_histories', 1);
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => $request['method'] === 'sendMessage'
            && $request['chat_id'] === 'test-admin-chat'
            && $request['parse_mode'] === 'HTML'
            && str_contains($request['text'], $title)
            && str_contains($request['text'], 'Иван &lt;Админ&gt; (client@example.com)')
            && str_contains($request['text'], '•••• 1234')
            && str_contains($request['text'], 'CardsPro ('.$type->value.')')
            && ! str_contains($request['text'], '4111111111111234'));
    }

    public function test_declined_block_does_not_notify_or_change_status(): void
    {
        $card = $this->createCard(CardStatus::Active);
        (new CardsProWebhookHandler($card->provider))->handle(CardsProCallbackType::CardBlock, [
            'san' => $card->provider_card_id,
            'status' => 'DECLINED',
        ]);

        $this->assertSame(CardStatus::Active, $card->fresh()->status);
        $this->assertDatabaseCount('card_status_histories', 0);
        Http::assertNothingSent();
    }

    public function test_unknown_card_does_not_notify(): void
    {
        $card = $this->createCard(CardStatus::Active);
        $handler = new CardsProWebhookHandler($card->provider);
        foreach ([CardsProCallbackType::CardBlock, CardsProCallbackType::CardFreeze, CardsProCallbackType::CardUnfreeze] as $type) {
            $handler->handle($type, ['san' => 'unknown-card', 'status' => 'EXECUTED']);
        }

        Http::assertNothingSent();
        $this->assertDatabaseCount('card_status_histories', 0);
    }

    public function test_telegram_failure_does_not_prevent_status_and_customer_notification(): void
    {
        Http::fake(['*' => Http::response(['ok' => false], 500)]);
        $card = $this->createCard(CardStatus::Active);
        (new CardsProWebhookHandler($card->provider))->handle(CardsProCallbackType::CardFreeze, [
            'san' => $card->provider_card_id,
        ]);

        $this->assertSame(CardStatus::Frozen, $card->fresh()->status);
        $this->assertDatabaseCount('card_status_histories', 1);
        $this->assertDatabaseHas('notifications', ['user_id' => $card->user_id, 'title' => 'Карта заморожена']);
        Http::assertSentCount(1);
    }

    private function createCard(CardStatus $status): Card
    {
        $provider = CardProvider::create(['name' => 'CardsPro', 'code' => 'cardspro']);
        $product = CardProduct::create(['name' => 'Test card', 'key' => 'test-card', 'currency' => 'USD', 'provider_id' => $provider->id]);
        $user = User::factory()->create(['name' => 'Иван <Админ>', 'email' => 'client@example.com']);

        return Card::create([
            'user_id' => $user->id,
            'card_product_id' => $product->id,
            'provider_id' => $provider->id,
            'provider_card_id' => 'test-san',
            'card_number' => '4111111111111234',
            'currency' => 'USD',
            'status' => $status,
        ]);
    }
}
