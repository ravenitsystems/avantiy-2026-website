<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    protected $table = 'team_member';

    protected $fillable = [
        'team_id',
        'user_id',
        'team_role_id',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teamRole(): BelongsTo
    {
        return $this->belongsTo(TeamRole::class, 'team_role_id');
    }

    /**
     * Check if this member is the team owner.
     * Falls back to checking team.user_id if no role is assigned.
     */
    public function isOwner(): bool
    {
        if ($this->teamRole) {
            return $this->teamRole->isOwner();
        }

        return $this->isTeamCreator();
    }

    /**
     * Check if this member has a specific permission.
     * Team creators get all permissions even without an assigned role.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->teamRole) {
            return $this->teamRole->hasPermission($permission);
        }

        return $this->isTeamCreator();
    }

    /** @return list<string> */
    public function permissionKeys(): array
    {
        if ($this->teamRole) {
            return $this->teamRole->permissionKeys();
        }

        if ($this->isTeamCreator()) {
            return Permission::orderBy('sort_order')->pluck('key')->all();
        }

        return [];
    }

    public function roleName(): string
    {
        if ($this->teamRole) {
            return $this->teamRole->name;
        }

        return $this->isTeamCreator() ? 'Owner' : 'Unknown';
    }

    private function isTeamCreator(): bool
    {
        $team = $this->team;

        return $team && (int) $team->user_id === (int) $this->user_id;
    }
}
