<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'channel',
        'subject',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
        ];
    }

    public function broadcasts(): HasMany
    {
        return $this->hasMany(Broadcast::class, 'template_id');
    }
}
