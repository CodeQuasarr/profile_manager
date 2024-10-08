<?php

namespace App\Models\Traits;

use App\Lib\TheCurrent;
use App\Models\Users\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait GlobalTrait
{
    /**
     * @description Scope to filter data by field and value
     *
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     * @return Builder
     */
    public function scopeFieldValue(Builder $query, string $field, $value): Builder
    {
        if (is_array($value) || $value instanceof Collection) {
            return $query->whereIn($field, $value);
        }
        return $query->where($field, $value);
    }

    /**
     * @description Hide fields according to user role
     *
     * @return array
     */
    public static function hideFields(): array
    {
        $fields = [];
        if (TheCurrent::user()) {
            $me = TheCurrent::user();
            if ($me->hasRole([Role::ADMINISTRATOR, Role::COACH, Role::PLAYER])) {
                $fields = [
                    'password',
                    'remember_token',
                ];
            }

            if ($me->hasRole([Role::COACH, Role::PLAYER])) {
                $fields = array_merge($fields, [ 'deleted_at', 'email_verified_at']);
            }

            if ($me->hasRole(Role::PLAYER)) {
                $fields = array_merge($fields, [
                    'status',
                    'created_at',
                    'updated_at',
                ]);
            }
        }

        return $fields;
    }
}
