<?php

namespace App\Models\Users;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\GlobalTrait;
use App\Observers\Users\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The ID of the user"
 *     ),
 *     @OA\Property(
 *         property="first_name",
 *         type="string",
 *         description="The first name of the user"
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         type="string",
 *         description="The last name of the user"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="The email address of the user"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="The date and time the user was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="The date and time the user was last updated"
 *     ),
 * )
 */
#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasApiTokens, GlobalTrait;

    /* CONSTANTS
    \**************************************************************************/
    public const STATUS_ACTIVE = 3;
    public const STATUS_PENDING = 2;
    public const STATUS_INACTIVE = 1;

    public const GAME_POSITION_POINT_GUARD = 'PG';
    public const GAME_POSITION_SHOOTING_GUARD = 'SG';
    public const GAME_POSITION_SMALL_FORWARD = 'SF';
    public const GAME_POSITION_POWER_FORWARD = 'PF';
    public const GAME_POSITION_CENTER = 'C';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'status', 'image', 'email_verified_at', 'coach_id',
        'weight', 'height', 'game_position',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'weight' => 'integer',
            'height' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('orderDefault', function (Builder $builder) {
            $builder
                ->orderBy("first_name", "ASC")
                ->orderBy("last_name", "ASC");

        });
//        static::addGlobalScope('withDefault', function (Builder $builder) {
//            $builder->with(["roles"]);
//        });

        static::saving(function ($user) {
           self::formatNames($user);
        });

        static::creating(function ($user) {
            self::formatNames($user);
        });
    }

    protected static function formatNames(User $user): void
    {
        $user->last_name = strtoupper($user->last_name);
        $user->first_name = ucfirst($user->first_name);
    }

    /* RELATIONS
   \**************************************************************************/

    public function invitations(): HasMany
    {
        return $this->hasMany(UsersInvitation::class, 'coach_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(User::class, 'coach_id');
    }

    public function teammates(): HasMany
    {
        return $this
            ->hasMany(User::class, 'coach_id', 'coach_id')
            ->where('status', 3);
    }

    /* SCOPES
    \**************************************************************************/

    /**
     * @description Scope a query to include only users that belong to the logged-in user's team.
     * If the logged-in user has the role of COACH or PLAYER, exclude users with the ADMIN role.
     * Otherwise, exclude users with the ADMIN and COACH roles and filter by active status.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLoggedUserTeam(Builder $query): Builder
    {
        if ($this->hasRole([Role::COACH, Role::PLAYER])) {
            $query->whereDoesntHave('roles', function ($query) {
                $query->where('name', Role::ADMINISTRATOR);
            });
            if ($this->hasRole(Role::PLAYER)) {
                $query->where('status', self::STATUS_ACTIVE);
            }
        } else {
            $query->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [Role::ADMINISTRATOR, Role::COACH]);
            })->fieldValue('status', self::STATUS_ACTIVE);
        }
        return $query;
    }

    /* METHODS
    \**************************************************************************/

    public static function getStatusName(int $status): string
    {
        return match ($status) {
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_INACTIVE => 'Inactif',
            default => 'Inconnu',
        };
    }

    public static function getPositionName(string $position): string
    {
        return match ($position) {
            self::GAME_POSITION_POINT_GUARD => 'Meneur',
            self::GAME_POSITION_SHOOTING_GUARD => 'Arrière',
            self::GAME_POSITION_SMALL_FORWARD => 'Ailier',
            self::GAME_POSITION_POWER_FORWARD => 'Ailier fort',
            self::GAME_POSITION_CENTER => 'Pivot',
            default => 'Inconnu',
        };
    }


    public function isAdminiStrator(): bool
    {
        return $this->hasRole(Role::ADMINISTRATOR);
    }

    public function isCoach(): bool
    {
        return $this->hasRole(Role::COACH);
    }

    public function isPlayer(): bool
    {
        return $this->hasRole(Role::PLAYER);
    }

    public static function getImageUrl(string $imageName): string
    {
        return asset('storage/images/' . $imageName);
    }



    /**
     * @description Recupère le nom complet de l'utilisateur.
     * @return string
     */
    public function getName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
