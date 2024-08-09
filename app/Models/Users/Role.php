<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use \Spatie\Permission\Models\Role as SpatieRole;
class Role extends SpatieRole
{
    use HasFactory;

    /* CONSTANTS
    \**************************************************************************/
    public const ADMINiSTRATOR = "administrator";
    public const COACH = "coach";
    public const PLAYER = "player";


    /* METHODS
    \**************************************************************************/

    /**
     * @description Réccupérer la liste des rôles
     * @return Collection
     */
    public static function static_getRoles(): Collection
    {
        return new Collection([
            self::ADMINiSTRATOR => __("roles.role_names.administrator"),
            self::COACH         => __("roles.role_names.coach"),
            self::PLAYER        => __("roles.role_names.player"),
        ]);
    }


}
