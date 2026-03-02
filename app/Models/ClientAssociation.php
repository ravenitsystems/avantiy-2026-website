<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAssociation extends Model
{
    protected $table = 'client_association';

    public const SCOPE_USER = 'user';

    public const SCOPE_TEAM = 'team';

    protected $fillable = [
        'client_id',
        'scope_type',
        'scope_user_id',
        'scope_team_id',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function scopeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scope_user_id');
    }

    public function scopeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'scope_team_id');
    }
}
