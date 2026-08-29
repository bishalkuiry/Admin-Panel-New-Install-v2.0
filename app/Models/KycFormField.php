<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KycFormField extends Model
{
    protected $fillable = [
        'target_role',
        'field_name',
        'field_label',
        'field_type',
        'is_required',
        'options',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'options' => 'array',
        'sort_order' => 'integer',
    ];
}
