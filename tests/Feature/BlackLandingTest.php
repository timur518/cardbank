<?php

namespace Tests\Feature;

use App\Models\CardProduct;
use App\Models\CardProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlackLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_uses_black_product_and_live_admin_tariffs(): void
    {
        $provider = CardProvider::create(['name' => 'Test', 'code' => 'test']);
        // Another product must never supply the price or conditions for Black.
        CardProduct::create([
            'provider_id' => $provider->id, 'key' => 'orange', 'name' => 'Orange',
            'currency' => 'USD', 'price_rub' => 98765, 'active' => true,
            'full_terms' => '<p>Условия другого продукта</p>',
        ]);
        $black = CardProduct::create([
            'provider_id' => $provider->id, 'key' => 'black', 'name' => 'Black',
            'currency' => 'USD', 'network' => 'mc', 'card_country' => 'US',
            'price_rub' => 1234, 'provider_topup_fee_percent' => 2.5,
            'successful_payment_fee_usd' => .15, 'decline_fee_usd' => .2,
            'risk_operation_fee_usd' => .3, 'non_usd_payment_fee' => '1% + $0.10',
            'topup_min_amount' => 10, 'topup_max_amount' => 1000,
            'issue_min_amount' => 5, 'issue_max_amount' => 500,
            'three_ds_supported' => true, 'active' => true, 'coming_soon' => false,
            'restricted_merchants' => '<p>Ограничения Black из админки</p>',
            'full_terms' => '<p>Полные условия Black из админки</p>',
        ]);

        $this->get(route('cards.black'))->assertOk()
            ->assertSee('1 234 ₽')->assertSee('2,50%')->assertSee('$0,15')
            ->assertSee('$0,20')->assertSee('$0,30')->assertSee('1% + $0.10')
            ->assertSee('$10,00 / $1 000,00')->assertSee('$5,00 / $500,00')
            ->assertSee('MasterCard')->assertSee('США')
            ->assertSee('Ограничения Black из админки')->assertSee('Полные условия Black из админки')
            ->assertDontSee('98 765 ₽')->assertDontSee('Условия другого продукта')
            ->assertSee(view('partials.site-header')->render(), false)
            ->assertSee(view('partials.site-footer')->render(), false)
            ->assertSee('https://mne.mojno.cc/register')->assertDontSee('utm_')
            ->assertSee('ChatGPT')->assertSee('Figma')->assertSee('Реквизитами онлайн')
            ->assertSee('3DS коды');

        $black->update(['price_rub' => 2345, 'successful_payment_fee_usd' => .45, 'three_ds_supported' => false]);
        $this->get('/cards/black')->assertOk()->assertSee('2 345 ₽')->assertSee('$0,45')
            ->assertDontSee('1 234 ₽')->assertDontSee('$0,15')
            ->assertDontSee('3DS коды')->assertSee('Не поддерживается');
        $this->get('/')->assertOk()->assertDontSee('/cards/black');
    }

    public function test_black_is_presented_as_online_only_even_if_wallet_flags_are_set(): void
    {
        $provider = CardProvider::create(['name' => 'Test', 'code' => 'test']);
        CardProduct::create([
            'provider_id' => $provider->id, 'key' => 'black', 'name' => 'Black', 'currency' => 'USD',
            'active' => true, 'apple_pay_enabled' => true, 'google_pay_enabled' => true, 'samsung_pay_enabled' => true,
        ]);

        $this->get('/cards/black')->assertOk()
            ->assertSee('Black используется только для онлайн-покупок по реквизитам.')
            ->assertSee('оплата у терминалов для этой карты недоступны.')
            ->assertDontSee('orange-wallet-panel')->assertDontSee('orange-phone-guide')
            ->assertDontSee('payments/apple-pay')->assertDontSee('payments/google-pay');
    }

    public function test_missing_inactive_and_coming_soon_black_do_not_offer_registration(): void
    {
        $this->get('/cards/black')->assertOk()->assertSee('Уточнить доступность')
            ->assertDontSee('https://mne.mojno.cc/register');

        $provider = CardProvider::create(['name' => 'Test', 'code' => 'test']);
        $black = CardProduct::create([
            'provider_id' => $provider->id, 'key' => 'black', 'name' => 'Black',
            'currency' => 'USD', 'price_rub' => 98765, 'active' => false,
        ]);
        $this->get('/cards/black')->assertOk()->assertDontSee('98 765 ₽')
            ->assertDontSee('https://mne.mojno.cc/register');

        $black->update(['active' => true, 'coming_soon' => true]);
        $this->get('/cards/black')->assertOk()->assertSee('98 765 ₽')->assertSee('Уточнить доступность')
            ->assertDontSee('https://mne.mojno.cc/register');
    }
}
