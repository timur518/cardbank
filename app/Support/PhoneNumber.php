<?php

namespace App\Support;

/**
 * Нормализация телефона, введённого через фронтовую маску (+7(999)123-45-67),
 * к виду, в котором он хранится и ищется в users.phone (+79991234567).
 */
class PhoneNumber
{
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return $phone;
        }

        $normalized = preg_replace('/[^\d+]/', '', $phone);

        return $normalized === '' ? null : $normalized;
    }
}
