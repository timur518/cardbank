<?php

namespace Tests\Feature;

use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Filament\Admin\Resources\PaymentMethods\Pages\EditPaymentMethod;
use App\Filament\Admin\Resources\PaymentMethods\Pages\ViewPaymentMethod;
use App\Filament\Admin\Resources\PaymentMethods\RelationManagers\MessagesRelationManager;
use App\Filament\Admin\Resources\PaymentMethods\RelationManagers\UsagesRelationManager;
use App\Models\Income;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodMessage;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentMethodHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create();
        $admin->assignRole(Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_history_shows_existing_incomes_for_this_method_newest_first_on_both_pages(): void
    {
        $method = $this->createMethod('Main');
        $otherMethod = $this->createMethod('Other');
        $new = $this->createIncome($method, '2026-10-09 12:00:00');
        $old = $this->createIncome($method, '2026-10-08 12:00:00');
        $other = $this->createIncome($otherMethod, '2026-10-10 12:00:00');
        $this->assertDatabaseCount('payment_method_usages', 0);

        foreach ([ViewPaymentMethod::class, EditPaymentMethod::class] as $page) {
            Livewire::test(UsagesRelationManager::class, ['ownerRecord' => $method, 'pageClass' => $page])
                ->assertCanSeeTableRecords([$new, $old], inOrder: true)
                ->assertCanNotSeeTableRecords([$other])
                ->assertTableColumnStateSet('payment_status', IncomePaymentStatus::Paid, $new);
        }
    }

    public function test_messages_show_newest_first_and_open_read_only_json_on_both_pages(): void
    {
        $method = $this->createMethod('Main');
        $payload = ['status' => 'paid', 'note' => 'Оплата <script>alert(1)</script>'];
        $new = PaymentMethodMessage::create(['payment_method_id' => $method->id, 'event_type' => 'PAYMENT', 'payload' => $payload, 'received_at' => '2026-10-09 12:00:00', 'status' => 'processed']);
        $old = PaymentMethodMessage::create(['payment_method_id' => $method->id, 'event_type' => 'PAYMENT', 'payload' => [], 'received_at' => '2026-10-08 12:00:00', 'status' => 'processed']);

        foreach ([ViewPaymentMethod::class, EditPaymentMethod::class] as $page) {
            $test = Livewire::test(MessagesRelationManager::class, ['ownerRecord' => $method, 'pageClass' => $page])
                ->assertCanSeeTableRecords([$new, $old], inOrder: true)
                ->assertActionVisible(TestAction::make('viewEvent')->table($new))
                ->mountAction(TestAction::make('viewEvent')->table($new))
                ->assertActionMounted(TestAction::make('viewEvent')->table($new))
                ->assertSchemaStateSet(['payload' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION)]);
            $schemaName = $test->instance()->getMountedActionSchemaName();
            $schema = $test->instance()->{$schemaName};
            $this->assertTrue($schema->getComponents()[0]->isDisabled());
            $this->assertStringNotContainsString('<script>alert(1)</script>', $schema->toHtml());
        }
        $this->assertSame($payload, $new->fresh()->payload);
    }

    private function createMethod(string $name): PaymentMethod
    {
        return PaymentMethod::create(['name' => $name, 'type' => 'gateway', 'currency' => 'USD']);
    }

    private function createIncome(PaymentMethod $method, string $date): Income
    {
        $income = Income::create(['payment_method_id' => $method->id, 'type' => IncomeType::CardTopup, 'amount' => 100, 'currency' => 'RUB', 'payment_status' => IncomePaymentStatus::Paid]);
        $income->forceFill(['created_at' => $date])->save();

        return $income;
    }
}
