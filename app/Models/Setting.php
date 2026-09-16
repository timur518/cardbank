<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Общее хранилище настроек панели «ключ — значение». Каждая страница из раздела
 * «Настройки» (бренд и сайт, реферальная программа, уведомления, аналитика) читает
 * и пишет сюда свой набор ключей, без отдельной таблицы под каждую страницу.
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Получить значение одной настройки.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    /**
     * Получить несколько настроек сразу в виде ['key' => 'value'].
     *
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    public static function getMany(array $keys): array
    {
        $values = static::query()->whereIn('key', $keys)->pluck('value', 'key')->toArray();

        return collect($keys)->mapWithKeys(fn (string $key) => [$key => $values[$key] ?? null])->toArray();
    }

    /**
     * Сохранить одну настройку.
     */
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Сохранить несколько настроек сразу из ['key' => 'value'].
     *
     * @param  array<string, mixed>  $values
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::set($key, $value);
        }
    }
}
