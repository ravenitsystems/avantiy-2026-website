<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeamRole extends Model
{
    protected $table = 'team_role';

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'is_owner',
        'is_preset',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_owner' => 'boolean',
            'is_preset' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'team_role_permission', 'team_role_id', 'permission_id');
    }

    public function isOwner(): bool
    {
        return (bool) $this->is_owner;
    }

    public function hasPermission(string $key): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return $this->permissions()->where('key', $key)->exists();
    }

    /** @return list<string> */
    public function permissionKeys(): array
    {
        if ($this->isOwner()) {
            return Permission::orderBy('sort_order')->pluck('key')->all();
        }

        return $this->permissions()->orderBy('sort_order')->pluck('key')->all();
    }
}
