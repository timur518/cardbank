<?php

namespace App\Models;

use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdPlacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'channel',
        'start_date',
        'end_date',
        'cost_amount',
        'comment',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'tracking_link',
        'clicks_count',
        'registrations_count',
        'paid_issuances_count',
        'revenue_amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'cost_amount' => 'decimal:2',
            'revenue_amount' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Пользователи, зарегистрировавшиеся с UTM-меткой кампании (User::utm_campaign), совпадающей
     * с указанной у этого размещения — источник для счётчиков регистраций и выручки ниже.
     * Утм-метки сохраняются на пользователе при регистрации из cookie лендинга/СПА (см. AuthController::register()/registerLanding()).
     */
    protected function matchedUsersQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        if (blank($this->utm_campaign)) {
            return null;
        }

        return User::query()->where('utm_campaign', $this->utm_campaign);
    }

    /**
     * Регистраций по этому размещению — считается на лету, а не хранится в БД (колонка
     * registrations_count в таблице никем не заполняется и всегда равна 0), поэтому перекрываем аксессором.
     */
    public function getRegistrationsCountAttribute(): int
    {
        return $this->matchedUsersQuery()?->count() ?? 0;
    }

    /**
     * Выручка от пользователей, пришедших по этой рекламной кампании (совпадение по utm_campaign) —
     * сумма оплаченных выпусков/пополнений карт в рублях (тот же фильтр, что и в ProfitStatsWidget::grossProfitStat()).
     * Так же, как и registrations_count, перекрывает статическую колонку revenue_amount, которая нигде не заполняется.
     */
    public function getRevenueAmountAttribute(): float
    {
        $userIds = $this->matchedUsersQuery()?->pluck('id');

        if (blank($userIds)) {
            return 0.0;
        }

        return (float) Income::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('type', [IncomeType::CardIssue, IncomeType::CardTopup])
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->where('currency', 'RUB')
            ->sum('amount');
    }

    /**
     * Окупаемость размещения в читаемом виде — сколько заработано по сравнению с потраченным
     * (для отображения в таблице).
     */
    public function getRoiLabelAttribute(): string
    {
        if ((float) $this->cost_amount <= 0) {
            return '—';
        }

        $roi = (((float) $this->revenue_amount - (float) $this->cost_amount) / (float) $this->cost_amount) * 100;

        return ($roi >= 0 ? '+' : '') . number_format($roi, 0, ',', ' ') . '%';
    }
}
