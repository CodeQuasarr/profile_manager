<?php

namespace App\Models\Users;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\Users\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property string $api_key
 * @property string $status
 * @property string $image
 */
#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasApiTokens;

    public const STATUS_ACTIVE = '3';
    public const STATUS_PENDING = '2';
    public const STATUS_INACTIVE = '1';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'api_key', 'status', 'image',
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
            'status' => 'integer',
        ];
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
