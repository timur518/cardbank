<?php

namespace App\Models;

use App\Enums\LegalDocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'version',
        'body',
        'effective_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => LegalDocumentType::class,
            'effective_at' => 'datetime',
        ];
    }
}
