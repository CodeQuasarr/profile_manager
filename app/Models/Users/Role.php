<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Role extends \Spatie\Permission\Models\Role
{
    use HasFactory;


    /* CONSTANTS
    \*******************************************************************************/
    const ADMIN = "administrator";
    const MANAGER = "manager";
    const USER = "user";


    /* METHODS
    \*******************************************************************************/

    /**
     * @description Retourne une collection des rôles disponibles avec leurs noms traduits.
     * @return Collection
     */
    public static function static_getRoles(): Collection
    {
        return new Collection([
            self::ADMIN => __("roles.roles_names.administrator"),
            self::MANAGER => __("roles.roles_names.immediate_supervisor"),
            self::USER => __("roles.roles_names.user"),
        ]);
    }

    /**
     * @description Retourne La traduction du nom du rôle.
     * @param string $name
     * @return string
     */
    public static function getDescriptionByName(string $name): string
    {
        return self::static_getRoles()->get($name);
    }

}
