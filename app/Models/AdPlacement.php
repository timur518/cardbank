<?php

namespace App\Models;

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
        'currency',
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
