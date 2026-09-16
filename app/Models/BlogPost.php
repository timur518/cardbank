<?php

namespace App\Models;

use App\Enums\BlogPostStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'cover_image_url',
        'body',
        'seo_title',
        'seo_description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => BlogPostStatus::class,
        ];
    }
}
