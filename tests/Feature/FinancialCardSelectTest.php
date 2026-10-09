<?php

namespace Tests\Feature;

use App\Filament\Admin\Forms\Components\CardSelect;
use App\Filament\Admin\Resources\Expenses\Pages\CreateExpense;
use App\Filament\Admin\Resources\Incomes\Pages\CreateIncome;
use App\Filament\Admin\Resources\Refunds\Pages\CreateRefund;
use App\Models\Card;
use App\Models\CardProduct;
use App\Models\CardProvider;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FinancialCardSelectTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_forms_search_card_numbers_and_preload_latest_ten_with_owner(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $owner = User::factory()->create(['first_name' => 'Иван', 'last_name' => 'Иванов', 'middle_name' => null]);
        $provider = CardProvider::create(['name' => 'Test', 'code' => 'test']);
        $product = CardProduct::create(['name' => 'Test', 'key' => 'test', 'currency' => 'USD', 'provider_id' => $provider->id]);
        $cards = collect();
        for ($i = 0; $i < 12; $i++) {
            $card = Card::create(['user_id' => $owner->id, 'provider_id' => $provider->id, 'card_product_id' => $product->id, 'card_number' => '4111111111'.sprintf('%06d', $i), 'currency' => 'USD', 'status' => 'active']);
            $card->forceFill(['created_at' => now()->subDays(12 - $i)])->save();
            $cards->push($card);
        }
        $target = $cards->first();
        foreach ([CreateIncome::class, CreateExpense::class, CreateRefund::class] as $page) {
            $test = Livewire::test($page)->assertSuccessful();
            $field = $test->instance()->form->getFlatFields()['card_id'];
            $this->assertInstanceOf(CardSelect::class, $field);
            $options = $field->getOptions();
            $this->assertSame($cards->reverse()->take(10)->pluck('id')->all(), array_keys($options));
            $this->assertStringContainsString('Иванов Иван', reset($options));
            foreach ([$target->card_number, '4111 1111 1100 0000', '4111-1111-1100-0000', '0000'] as $search) {
                $this->assertSame([$target->id], array_keys($field->getSearchResults($search)));
            }
            $this->assertSame([], $field->getSearchResults('1111'));
            $this->assertSame([], $field->getSearchResults('%'));
            $test->fillForm(['card_id' => $target->id]);
            $field = $test->instance()->form->getFlatFields()['card_id'];
            $this->assertStringContainsString('Иванов Иван', $field->getOptionLabel());
            $this->assertStringContainsString('0000', $field->getOptionLabel());
        }
    }
}
