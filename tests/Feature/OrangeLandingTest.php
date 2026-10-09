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
            'google_pay_enabled' => true, 'active' => true, 'coming_soon' => false,
        ]);

        $this->get('/cards/orange')->assertOk()
            ->assertSee('1 234 ₽')->assertSee('2,50%')->assertSee('$0,15')
            ->assertSee('https://mne.mojno.cc/register')->assertSee('Apple Pay')->assertSee('Google Pay')
            ->assertDontSee('utm_');
        $product->update(['price_rub' => 2345]);
        $this->get('/cards/orange')->assertOk()->assertSee('2 345 ₽')->assertDontSee('1 234 ₽');
        $this->get('/')->assertOk()->assertDontSee('/cards/orange');
    }

    public function test_missing_or_inactive_product_does_not_offer_registration_or_invent_price(): void
    {
        $this->get('/cards/orange')->assertOk()->assertSee('Уточнить доступность')
            ->assertDontSee('https://mne.mojno.cc/register');
        $provider = CardProvider::create(['name' => 'Test', 'code' => 'test']);
        CardProduct::create(['provider_id' => $provider->id, 'key' => 'orange', 'name' => 'Orange', 'currency' => 'USD', 'price_rub' => 98765, 'active' => false]);
        $this->get('/cards/orange')->assertOk()->assertSee('Уточнить доступность')->assertDontSee('98 765 ₽');
    }
}
