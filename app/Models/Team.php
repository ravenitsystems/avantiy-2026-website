<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $table = 'team';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'deleted',
        'deleted_at',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            if (Schema::hasColumn((new static)->getTable(), 'deleted')) {
                $builder->where('deleted', false);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class, 'team_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'team_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TeamMessage::class, 'team_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(TeamRole::class, 'team_id');
    }

    /**
     * All roles available to this team: team-specific custom roles + global presets.
     */
    public function allRoles(): \Illuminate\Database\Eloquent\Builder
    {
        return TeamRole::where('team_id', $this->id)->orWhereNull('team_id');
    }

    public function memberRecord(int $userId): ?TeamMember
    {
        return $this->members()->where('user_id', $userId)->first();
    }
}
