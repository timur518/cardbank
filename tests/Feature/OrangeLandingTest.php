<?php

namespace Tests\Feature;

use App\Models\CardProduct;
use App\Models\CardProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrangeLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_uses_current_orange_tariffs_and_registration_link(): void
    {
        $provider = CardProvider::create(['name' => 'Test', 'code' => 'test']);
        $product = CardProduct::create([
            'provider_id' => $provider->id, 'key' => 'orange', 'name' => 'Orange',
            'currency' => 'USD', 'price_rub' => 1234, 'provider_topup_fee_percent' => 2.5,
            'successful_payment_fee_usd' => 0.15, 'apple_pay_enabled' => true,
            'google_pay_enabled' => true, 'samsung_pay_enabled' => true, 'three_ds_supported' => true, 'active' => true, 'coming_soon' => false,
            'description' => 'Описание именно Orange', 'full_terms' => '<p>Условия Orange из админки</p>',
        ]);

        $product->forceFill(['id' => 6])->save();
        CardProduct::create(['provider_id' => $provider->id, 'key' => 'white', 'name' => 'White', 'currency' => 'EUR', 'price_rub' => 98765, 'full_terms' => '<p>Условия White</p>']);

        $this->get('/cards/orange')->assertOk()
            ->assertDontSee('Описание именно Orange')->assertSee('Условия Orange из админки')->assertDontSee('Условия White')
            ->assertSee('data-site-header', false)->assertDontSee('class="orange-nav"', false)->assertSee('Бесплатное обслуживание')->assertSee('обслуживание в месяц')
            ->assertSee(view('partials.site-footer')->render(), false)
            ->assertSee('Авиа и ЖД билеты')->assertSee('eSIM для путешествий')->assertDontSee('Хотите уточнить, подойдёт ли Orange для вашей покупки?')
            ->assertDontSee('Что делать, если оплата не прошла?')->assertSee('Уточните правила отеля')
            ->assertSee('1 234 ₽')->assertSee('2,50%')->assertSee('$0,15')
            ->assertSee('https://mne.mojno.cc/register')->assertSee('Apple Pay')->assertSee('Google Pay')->assertSee('Samsung Pay')
            ->assertSee('Booking.com')->assertSee('Airbnb')->assertSee('Откройте Samsung Wallet')
            ->assertSee('Откройте приложение Wallet')->assertSee('Откройте Google Wallet')
            ->assertSee('Платёжный адрес')->assertSee('3DS коды')
            ->assertDontSee('utm_');
        $product->update(['price_rub' => 2345]);
        $this->get('/cards/orange')->assertOk()->assertSee('2 345 ₽')->assertDontSee('1 234 ₽');
        $product->update(['google_pay_enabled' => false, 'samsung_pay_enabled' => false]);
        $this->get('/cards/orange')->assertOk()->assertSee('Добавьте вашу карту в Apple Pay.')
            ->assertDontSee('Добавьте вашу карту в Apple Pay и Google Pay.');
        $product->update(['apple_pay_enabled' => false]);
        $this->get('/cards/orange')->assertOk()->assertSee('Оплата через Apple Pay, Google Pay и Samsung Pay для этой карты сейчас недоступна.')
            ->assertDontSee('Добавьте вашу карту в Apple Pay.')->assertDontSee('Откройте приложение Wallet')
            ->assertDontSee('Откройте Google Wallet')->assertDontSee('Откройте Samsung Wallet');
        $product->update(['samsung_pay_enabled' => true]);
        $this->get('/cards/orange')->assertOk()->assertSee('Добавьте вашу карту в Samsung Pay.')
            ->assertSee('Откройте Samsung Wallet')->assertDontSee('Откройте приложение Wallet');
        $this->get('/')->assertOk()->assertDontSee('/cards/orange');
    }

    public function test_migration_enables_samsung_pay_only_for_the_confirmed_orange_product(): void
    {
        $provider = CardProvider::create(['name' => 'Test', 'code' => 'test']);
        $orange = CardProduct::create(['provider_id' => $provider->id, 'key' => 'orange', 'name' => 'Orange', 'currency' => 'USD']);
        $orange->forceFill(['id' => 6])->save();
        $white = CardProduct::create(['provider_id' => $provider->id, 'key' => 'white', 'name' => 'White', 'currency' => 'USD']);

        $migration = require database_path('migrations/2026_10_09_100001_add_samsung_pay_to_card_products_table.php');
        $migration->down();
        $migration->up();

        $this->assertTrue($orange->fresh()->samsung_pay_enabled);
        $this->assertFalse($white->fresh()->samsung_pay_enabled);
    }

    public function test_missing_or_inactive_product_does_not_offer_registration_or_invent_price(): void
    {
        $this->get('/cards/orange')->assertOk()->assertSee('Уточнить доступность')
            ->assertDontSee('https://mne.mojno.cc/register');
        $provider = CardProvider::create(['name' => 'Test', 'code' => 'test']);
        $product = CardProduct::create(['provider_id' => $provider->id, 'key' => 'orange', 'name' => 'Orange', 'currency' => 'USD', 'price_rub' => 98765, 'active' => false]);
        $product->forceFill(['id' => 6])->save();
        $this->get('/cards/orange')->assertOk()->assertSee('Уточнить доступность')->assertDontSee('98 765 ₽');
    }
}
