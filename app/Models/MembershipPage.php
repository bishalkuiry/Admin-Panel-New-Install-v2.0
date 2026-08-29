<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'sections_data',
        'is_published',
    ];

    protected $casts = [
        'sections_data' => 'array',
        'is_published' => 'boolean',
    ];
}
