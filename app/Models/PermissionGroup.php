<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionGroup extends Model
{
    protected $table = 'permission_group';

    protected $fillable = [
        'key',
        'name',
        'description',
        'sort_order',
        'is_high_impact',
    ];

    protected function casts(): array
    {
        return [
            'is_high_impact' => 'boolean',
        ];
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'permission_group_id');
    }
}
