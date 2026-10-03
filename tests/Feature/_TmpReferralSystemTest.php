<?php

namespace Tests\Feature;

use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Enums\PartnerTransactionType;
use App\Enums\PayoutRequestStatus;
use App\Models\Income;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\PayoutRequest;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payments\PaymentWebhookHandler;
use App\Services\Referral\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class _TmpReferralSystemTest extends TestCase
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

    public function test_registration_accrues_fixed_bonus_to_referrer(): void
    {
        Setting::set('referral_registration_bonus_usd', '2.5');

        $referrer = User::factory()->create();
        $referred = User::factory()->create(['referral_code' => $referrer->invite_code]);

        ReferralService::accrueForRegistration($referred);

        $this->assertDatabaseHas('partner_transactions', [
            'partner_user_id' => $referrer->id,
            'buyer_user_id' => $referred->id,
            'type' => PartnerTransactionType::Registration->value,
            'income_id' => null,
            'commission_amount' => '2.50',
        ]);
    }

    public function test_registration_bonus_not_accrued_twice(): void
    {
        Setting::set('referral_registration_bonus_usd', '2.5');

        $referrer = User::factory()->create();
        $referred = User::factory()->create(['referral_code' => $referrer->invite_code]);

        ReferralService::accrueForRegistration($referred);
        ReferralService::accrueForRegistration($referred);

        $this->assertSame(1, PartnerTransaction::where('buyer_user_id', $referred->id)->count());
    }

    public function test_registration_accrues_nothing_without_bonus_configured(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create(['referral_code' => $referrer->invite_code]);

        ReferralService::accrueForRegistration($referred);

        $this->assertSame(0, PartnerTransaction::count());
    }

    public function test_self_referral_is_ignored(): void
    {
        Setting::set('referral_registration_bonus_usd', '2.5');

        $user = User::factory()->create();
        $user->update(['referral_code' => $user->invite_code]);

        ReferralService::accrueForRegistration($user);

        $this->assertSame(0, PartnerTransaction::count());
    }

    public function test_paid_income_accrues_percentage_commission_to_referrer(): void
    {
        Setting::setMany([
            'referral_issue_rate' => '10',
            'referral_topup_rate' => '5',
        ]);

        $referrer = User::factory()->create();
        $buyer = User::factory()->create(['referral_code' => $referrer->invite_code]);

        $issueIncome = Income::create([
            'user_id' => $buyer->id,
            'type' => IncomeType::CardIssue,
            'amount' => 100,
            'currency' => 'USD',
            'amount_usd' => 100,
            'payment_status' => IncomePaymentStatus::Pending,
        ]);

        ReferralService::accrueForIncome($issueIncome);

        $this->assertDatabaseHas('partner_transactions', [
            'partner_user_id' => $referrer->id,
            'buyer_user_id' => $buyer->id,
            'type' => PartnerTransactionType::CardIssue->value,
            'income_id' => $issueIncome->id,
            'rate' => '10.00',
            'commission_amount' => '10.00',
        ]);

        $topupIncome = Income::create([
            'user_id' => $buyer->id,
            'type' => IncomeType::CardTopup,
            'amount' => 200,
            'currency' => 'USD',
            'amount_usd' => 200,
            'payment_status' => IncomePaymentStatus::Pending,
        ]);

        ReferralService::accrueForIncome($topupIncome);

        $this->assertDatabaseHas('partner_transactions', [
            'partner_user_id' => $referrer->id,
            'buyer_user_id' => $buyer->id,
            'type' => PartnerTransactionType::CardTopup->value,
            'income_id' => $topupIncome->id,
            'rate' => '5.00',
            'commission_amount' => '10.00',
        ]);
    }

    public function test_income_accrual_is_idempotent_per_income(): void
    {
        Setting::set('referral_issue_rate', '10');

        $referrer = User::factory()->create();
        $buyer = User::factory()->create(['referral_code' => $referrer->invite_code]);

        $income = Income::create([
            'user_id' => $buyer->id,
            'type' => IncomeType::CardIssue,
            'amount' => 100,
            'currency' => 'USD',
            'amount_usd' => 100,
            'payment_status' => IncomePaymentStatus::Pending,
        ]);

        ReferralService::accrueForIncome($income);
        ReferralService::accrueForIncome($income);

        $this->assertSame(1, PartnerTransaction::where('income_id', $income->id)->count());
    }

    public function test_payment_webhook_handler_triggers_referral_accrual_on_paid_income(): void
    {
        Setting::set('referral_issue_rate', '10');

        $referrer = User::factory()->create();
        $buyer = User::factory()->create(['referral_code' => $referrer->invite_code]);

        $income = Income::create([
            'user_id' => $buyer->id,
            'type' => IncomeType::CardIssue,
            'amount' => 50,
            'currency' => 'USD',
            'amount_usd' => 50,
            'payment_status' => IncomePaymentStatus::Pending,
            'card_id' => null,
            'payment_transaction_id' => 'txn-referral-test',
        ]);

        app(PaymentWebhookHandler::class)->handle([
            'transaction_id' => 'txn-referral-test',
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('partner_transactions', [
            'income_id' => $income->id,
            'partner_user_id' => $referrer->id,
            'commission_amount' => '5.00',
        ]);
    }

    public function test_partner_model_lists_only_users_with_referrals(): void
    {
        $referrer = User::factory()->create();
        User::factory()->create(['referral_code' => $referrer->invite_code]);
        User::factory()->create(); // не партнёр — никого не пригласил

        $partnerIds = Partner::query()->pluck('id')->all();

        $this->assertSame([$referrer->id], $partnerIds);
    }

    public function test_partner_aggregated_stats_are_correct(): void
    {
        Setting::setMany(['referral_issue_rate' => '10', 'referral_registration_bonus_usd' => '1']);

        $referrer = User::factory()->create();
        $payingBuyer = User::factory()->create(['referral_code' => $referrer->invite_code]);
        $silentBuyer = User::factory()->create(['referral_code' => $referrer->invite_code]);

        Income::create([
            'user_id' => $payingBuyer->id,
            'type' => IncomeType::CardIssue,
            'amount' => 100,
            'currency' => 'USD',
            'amount_usd' => 100,
            'payment_status' => IncomePaymentStatus::Paid,
        ]);

        PartnerTransaction::create([
            'partner_user_id' => $referrer->id,
            'buyer_user_id' => $payingBuyer->id,
            'type' => PartnerTransactionType::CardIssue,
            'rate' => 10,
            'commission_amount' => 10,
        ]);
        PartnerTransaction::create([
            'partner_user_id' => $referrer->id,
            'buyer_user_id' => $silentBuyer->id,
            'type' => PartnerTransactionType::Registration,
            'rate' => 1,
            'commission_amount' => 1,
        ]);

        PayoutRequest::create([
            'user_id' => $referrer->id,
            'amount_usd' => 4,
            'amount_rub' => 400,
            'destination' => 'bank_card',
            'status' => PayoutRequestStatus::Paid,
        ]);
        PayoutRequest::create([
            'user_id' => $referrer->id,
            'amount_usd' => 2,
            'amount_rub' => 200,
            'destination' => 'bank_card',
            'status' => PayoutRequestStatus::Pending,
        ]);

        $partner = Partner::query()->findOrFail($referrer->id);

        $this->assertSame(2, $partner->referredUsers()->count());
        $this->assertSame(1, $partner->activeReferredUsersCount());
        $this->assertEquals(11.0, $partner->totalEarnedUsd());
        $this->assertEquals(2.0, $partner->pendingPayoutUsd());
        $this->assertEquals(4.0, $partner->paidPayoutUsd());
        $this->assertEquals(5.0, $partner->availableBalanceUsd());
    }

    public function test_admin_partners_pages_are_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $referrer = User::factory()->create();
        User::factory()->create(['referral_code' => $referrer->invite_code]);

        $this->get('/admin/partners')->assertOk();
        $this->get("/admin/partners/{$referrer->id}")->assertOk();
    }

    public function test_admin_partners_table_does_not_show_invite_code(): void
    {
        $this->actingAsSuperAdmin();

        $referrer = User::factory()->create(['name' => 'Иван Партнёров']);
        User::factory()->create(['referral_code' => $referrer->invite_code]);

        $this->get('/admin/partners')
            ->assertOk()
            ->assertSee('Иван Партнёров')
            ->assertSee($referrer->email)
            ->assertDontSee("Код: {$referrer->invite_code}");
    }

    public function test_admin_partners_table_merges_invited_and_active_counts(): void
    {
        $this->actingAsSuperAdmin();

        $weakPartner = User::factory()->create();
        User::factory()->create(['referral_code' => $weakPartner->invite_code]);

        $strongPartner = User::factory()->create();
        $buyer1 = User::factory()->create(['referral_code' => $strongPartner->invite_code]);
        $buyer2 = User::factory()->create(['referral_code' => $strongPartner->invite_code]);
        Income::create([
            'user_id' => $buyer1->id,
            'type' => IncomeType::CardIssue,
            'amount' => 10,
            'currency' => 'USD',
            'amount_usd' => 10,
            'payment_status' => IncomePaymentStatus::Paid,
        ]);
        Income::create([
            'user_id' => $buyer2->id,
            'type' => IncomeType::CardIssue,
            'amount' => 10,
            'currency' => 'USD',
            'amount_usd' => 10,
            'payment_status' => IncomePaymentStatus::Pending,
        ]);

        $this->get('/admin/partners')
            ->assertOk()
            ->assertSee('1 / 2')
            ->assertSee('0 / 1');
    }

    public function test_partners_table_sort_query_orders_by_total_then_active_referrals(): void
    {
        $weakPartner = User::factory()->create();
        User::factory()->create(['referral_code' => $weakPartner->invite_code]);

        $strongPartner = User::factory()->create();
        User::factory()->create(['referral_code' => $strongPartner->invite_code]);
        User::factory()->create(['referral_code' => $strongPartner->invite_code]);

        // То же самое withCount + orderBy, что и в TextColumn::sortable() в PartnersTable.
        $ascending = Partner::query()
            ->withCount('referredUsers')
            ->orderBy('referred_users_count', 'asc')
            ->pluck('id')
            ->all();

        $this->assertSame([$weakPartner->id, $strongPartner->id], $ascending);

        $descending = Partner::query()
            ->withCount('referredUsers')
            ->orderBy('referred_users_count', 'desc')
            ->pluck('id')
            ->all();

        $this->assertSame([$strongPartner->id, $weakPartner->id], $descending);
    }

    public function test_admin_partner_transactions_pages_are_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get('/admin/partner-transactions')->assertOk();
        $this->get('/admin/partner-transactions/create')->assertOk();
    }

    public function test_admin_partner_transactions_table_has_renamed_and_removed_columns(): void
    {
        $this->actingAsSuperAdmin();

        $referrer = User::factory()->create();
        $buyer = User::factory()->create(['referral_code' => $referrer->invite_code]);

        PartnerTransaction::create([
            'partner_user_id' => $referrer->id,
            'buyer_user_id' => $buyer->id,
            'type' => PartnerTransactionType::Registration,
            'income_id' => null,
            'rate' => 1,
            'commission_amount' => 1,
        ]);

        $response = $this->get('/admin/partner-transactions')->assertOk();

        $response->assertSee('ID операции', false);
        $response->assertDontSee('ID операции в поступлениях');
        $response->assertSee('Дата', false);
        $response->assertDontSee('Когда создано');
        $response->assertDontSee('Когда отредактировано');
    }

    public function test_admin_partner_transaction_view_and_edit_render_for_both_income_and_registration_types(): void
    {
        $this->actingAsSuperAdmin();

        $referrer = User::factory()->create();
        $buyer = User::factory()->create(['referral_code' => $referrer->invite_code]);

        $income = Income::create([
            'user_id' => $buyer->id,
            'type' => IncomeType::CardIssue,
            'amount' => 100,
            'currency' => 'USD',
            'amount_usd' => 100,
            'payment_status' => IncomePaymentStatus::Paid,
        ]);

        $issueTx = PartnerTransaction::create([
            'partner_user_id' => $referrer->id,
            'buyer_user_id' => $buyer->id,
            'type' => PartnerTransactionType::CardIssue,
            'income_id' => $income->id,
            'rate' => 10,
            'commission_amount' => 10,
        ]);

        $registrationTx = PartnerTransaction::create([
            'partner_user_id' => $referrer->id,
            'buyer_user_id' => $buyer->id,
            'type' => PartnerTransactionType::Registration,
            'income_id' => null,
            'rate' => 1,
            'commission_amount' => 1,
        ]);

        $this->get("/admin/partner-transactions/{$issueTx->id}")->assertOk()->assertSee((string) $income->id);
        $this->get("/admin/partner-transactions/{$issueTx->id}/edit")->assertOk();

        $this->get("/admin/partner-transactions/{$registrationTx->id}")->assertOk();
        $this->get("/admin/partner-transactions/{$registrationTx->id}/edit")->assertOk();
    }

    public function test_admin_payout_requests_page_is_accessible(): void
    {
        $this->actingAsSuperAdmin();

        $this->get('/admin/payout-requests')->assertOk();
        $this->get('/admin/payout-requests/create')->assertOk();
    }

    public function test_admin_payout_request_view_and_edit_render_for_bank_card_and_wallet_destinations(): void
    {
        $this->actingAsSuperAdmin();

        $partner = User::factory()->create();

        $bankCardPayout = PayoutRequest::create([
            'user_id' => $partner->id,
            'amount_usd' => 10,
            'amount_rub' => 1000,
            'destination' => \App\Enums\PayoutDestination::BankCard,
            'bank_name' => 'Тинькофф Банк',
            'bank_card_number' => '2200000000000000',
            'bank_card_holder' => 'IVAN IVANOV',
            'status' => PayoutRequestStatus::Pending,
        ]);

        $walletPayout = PayoutRequest::create([
            'user_id' => $partner->id,
            'amount_usd' => 5,
            'amount_rub' => 500,
            'destination' => \App\Enums\PayoutDestination::Wallet,
            'status' => PayoutRequestStatus::Pending,
        ]);

        // С выводом на карту — блок "Реквизиты карты" должен быть виден.
        $this->get("/admin/payout-requests/{$bankCardPayout->id}")
            ->assertOk()
            ->assertSee('Тинькофф Банк');
        $this->get("/admin/payout-requests/{$bankCardPayout->id}/edit")->assertOk();

        // С выводом на внутренний счёт — блок реквизитов скрыт, страница рендерится без ошибок.
        $this->get("/admin/payout-requests/{$walletPayout->id}")
            ->assertOk()
            ->assertDontSee('Тинькофф Банк');
        $this->get("/admin/payout-requests/{$walletPayout->id}/edit")->assertOk();
    }

    public function test_admin_referral_settings_page_has_new_field(): void
    {
        $this->actingAsSuperAdmin();

        $this->get('/admin/referral-settings')
            ->assertOk()
            ->assertSee('referral_registration_bonus_usd', false);
    }
}
