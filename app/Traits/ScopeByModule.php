<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait ScopeByModule
{
    public static function bootScopeByModule()
    {
        // 1. Global query scoping for active module in session
        static::addGlobalScope('active_module', function (Builder $builder) {
            if (session()->has('admin_active_module') && session('admin_active_module') !== 'all') {
                $table = $builder->getModel()->getTable();
                $builder->where("{$table}.module_id", session('admin_active_module'));
            }
        });

        // 2. Auto-assign module_id on model creation if omitted
        static::creating(function (Model $model) {
            if (empty($model->module_id) && session()->has('admin_active_module') && session('admin_active_module') !== 'all') {
                $model->module_id = session('admin_active_module');
            }
        });
    }
}
