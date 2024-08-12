<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Collection;
use \Illuminate\Support\Collection as IlluminateCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    public const READ_MY_PROFILE = 'read_my_profile';
    public const UPDATE_MY_PROFILE = 'update_my_profile';
    public const DELETE_MY_PROFILE = 'delete_my_profile';

    public const READ_USERS = 'read_users';
    public const CREATE_USERS = 'create_users';
    public const UPDATE_USERS = 'update_users';
    public const DELETE_USERS = 'delete_users';
    public const FORCE_DELETE_USERS = 'force_delete_users';


    /**
     * @description Réccupérer la liste des permissions
     * @return Collection
     */
    public static function static_getPermissions(): IlluminateCollection {
        return new Collection([
            self::READ_MY_PROFILE => __("permissions.permission_names.read_my_profile"),
            self::UPDATE_MY_PROFILE => __("permissions.permission_names.update_my_profile"),
            self::DELETE_MY_PROFILE => __("permissions.permission_names.delete_my_profile"),

            self::READ_USERS => __("permissions.permission_names.read_users"),
            self::CREATE_USERS => __("permissions.permission_names.create_users"),
            self::UPDATE_USERS => __("permissions.permission_names.update_users"),
            self::DELETE_USERS => __("permissions.permission_names.delete_users"),
            self::FORCE_DELETE_USERS => __("permissions.permission_names.force_delete_users"),
        ]);
    }

    /**
     * @description Réccupérer les permissions par rôle
     * @param string $roleName
     * @return array|Collection|null
     */
    public static function static_getPermissionsByRoleName(string $roleName): array|Collection|null
    {
        return match ($roleName) {
            Role::ADMINISTRATOR => self::all(),
            Role::COACH => self::static_getPermissions_coach(),
            Role::PLAYER => self::static_getPermissions_player(),
            default => null,
        };
    }

    /**
     * @description Réccupérer les permissions pour le rôle coach
     * @return string[]
     */
    private static function static_getPermissions_coach(): array
    {
        return [
            self::READ_MY_PROFILE,
            self::UPDATE_MY_PROFILE,
            self::DELETE_MY_PROFILE,

            self::READ_USERS,
            self::CREATE_USERS,
            self::UPDATE_USERS,
            self::DELETE_USERS,
        ];
    }

    /**
     * @description Réccupérer les permissions pour le rôle player
     * @return string[]
     */
    private static function static_getPermissions_player(): array
    {
        return [
            self::READ_MY_PROFILE,

            self::READ_USERS,
        ];
    }
}
