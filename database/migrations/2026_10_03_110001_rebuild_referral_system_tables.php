<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Переводит реферальную систему с ручного заведения «партнёров» (таблица `partners`,
 * заполнялась администратором руками) на автоматическую: партнёром считается любой
 * пользователь, у которого есть хотя бы один приглашённый (другой пользователь с
 * users.referral_code = его users.invite_code) — см. App\Models\Partner (наследует
 * User, отдельной таблицы под него больше нет).
 *
 * На момент миграции обе таблицы (`partners`, `payout_requests`) пусты (стадия
 * заготовки), поэтому пересоздаются с нуля без переноса данных.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('partners');

        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Сумма списания с партнёрского баланса (в той же валюте, что и начисления
            // в partner_transactions.commission_amount) — именно она, а не amount_rub,
            // участвует в расчёте «доступно к выводу» / «ожидает к выплате» / «выплачено».
            $table->decimal('amount_usd', 12, 2);
            // Фактическая сумма перевода партнёру (банковской картой/на кошелёк) — вводится
            // администратором вручную по актуальному курсу на момент одобрения заявки.
            $table->decimal('amount_rub', 12, 2);
            $table->string('destination'); // wallet, bank_card
            $table->string('bank_card_number')->nullable();
            $table->string('bank_card_holder')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
        });

        Schema::create('partner_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // card_issue, card_topup, registration
            // Операция в реестре поступлений (incomes), за которую начислено вознаграждение.
            // null для типа registration — там нет связанного поступления.
            $table->foreignId('income_id')->nullable()->constrained('incomes')->nullOnDelete();
            // Ставка на момент начисления: % от суммы (card_issue/card_topup) либо
            // фиксированная сумма в $ (registration) — см. ReferralSettings.
            $table->decimal('rate', 8, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_transactions');
        Schema::dropIfExists('payout_requests');

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('invite_link')->nullable();
            $table->unsignedInteger('referrals_count')->default(0);
            $table->unsignedInteger('paying_count')->default(0);
            $table->decimal('available_usd', 12, 2)->default(0);
            $table->decimal('hold_usd', 12, 2)->default(0);
            $table->decimal('requested_usd', 12, 2)->default(0);
            $table->decimal('paid_usd', 12, 2)->default(0);
            $table->decimal('lifetime_usd', 12, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_rub', 12, 2);
            $table->string('destination');
            $table->string('bank_card_number')->nullable();
            $table->string('bank_card_holder')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
        });
    }
};
