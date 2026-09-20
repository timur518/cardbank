<?php

namespace App\Models;

use App\Enums\MerchantCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Справочник мерчантов — по подстроке в {@see CardTransaction::merchant} определяем,
 * какой это сервис, чтобы показать клиенту понятное название, логотип и цвет вместо
 * сырой строки эмитента вида "AUGMENT CODE           PALO ALTO     USA".
 */
class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'logo_svg',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => MerchantCategory::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Активные мерчанты, загруженные один раз за процесс — используется в
     * {@see self::matchByDescription()}, который вызывается на каждую транзакцию
     * (в т.ч. пачками при синхронизации истории), чтобы не бить в базу по разу
     * на операцию.
     *
     * @var Collection<int, self>|null
     */
    protected static ?Collection $matchingCache = null;

    /**
     * Сбросить кэш активных мерчантов — вызывать после изменения справочника
     * мерчантов в рамках одного процесса (например, в тестах).
     */
    public static function forgetMatchingCache(): void
    {
        static::$matchingCache = null;
    }

    /**
     * Определяет мерчанта по подстроке кода в описании операции от провайдера,
     * например код "AUGMENT CODE" находится в "AUGMENT CODE           PALO ALTO
     * USA". Если подстрок несколько подходящих — выбирается мерчант с самым
     * длинным (то есть самым специфичным) кодом. Регистр не важен.
     */
    public static function matchByDescription(?string $description): ?self
    {
        $description = trim((string) $description);

        if ($description === '') {
            return null;
        }

        $haystack = mb_strtoupper($description);

        static::$matchingCache ??= static::query()->where('is_active', true)->get(['id', 'code']);

        return static::$matchingCache
            ->filter(fn (self $merchant) => $merchant->code !== '' && str_contains($haystack, mb_strtoupper($merchant->code)))
            ->sortByDesc(fn (self $merchant) => mb_strlen($merchant->code))
            ->first();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CardTransaction::class);
    }
}
