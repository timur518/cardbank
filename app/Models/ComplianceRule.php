<?php

namespace App\Models;

use App\Enums\ComplianceRuleAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceRule extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'condition',
        'threshold',
        'action',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'threshold' => 'decimal:2',
            'action' => ComplianceRuleAction::class,
            'active' => 'boolean',
        ];
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(ComplianceAlert::class, 'rule_id');
    }
}
