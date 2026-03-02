<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $table = 'permission';

    protected $fillable = [
        'key',
        'label',
        'description',
        'permission_group_id',
        'is_high_impact',
        'sort_order',
    ];

    public function permissionGroup(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }

    protected function casts(): array
    {
        return [
            'is_high_impact' => 'boolean',
        ];
    }
}
