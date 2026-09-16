<?php

namespace App\Models;

use App\Enums\BannerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_url',
        'link_url',
        'title',
        'start_date',
        'end_date',
        'sort',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => BannerStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
