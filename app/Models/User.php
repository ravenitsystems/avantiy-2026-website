<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'user';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'duda_username',
        'stripe_username',
        'account_type',
        'billed_until',
        'amount_charged',
        'payment_term',
        'admin_code',
        'pricing_group',
        'marketing',
        'address_line_1',
        'address_line_2',
        'city',
        'state_region',
        'postal_code',
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'account_type' => AccountType::class,
            'billed_until' => 'date',
            'deleted' => 'boolean',
            'deleted_at' => 'datetime',
            'amount_charged' => 'decimal:2',
            'marketing' => 'boolean',
        ];
    }

    /**
     * Teams owned by this user (created by them).
     */
    public function teams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Team::class, 'user_id');
    }

    /**
     * Team memberships (owned + joined).
     */
    public function teamMemberships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeamMember::class, 'user_id');
    }
}
