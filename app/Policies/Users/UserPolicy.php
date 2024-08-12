<?php

namespace App\Policies\Users;

use App\Models\Users\Role;
use App\Models\Users\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function before(User $user, string $ability): bool|null
    {

        return null;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole([Role::COACH, Role::PLAYER, Role::ADMINISTRATOR]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->hasRole(Role::ADMINISTRATOR)) {
            return true;
        }
        return !$model->hasRole(Role::ADMINISTRATOR);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole([Role::COACH, Role::ADMINISTRATOR]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->hasRole([Role::ADMINISTRATOR, Role::COACH])) {
            return $this->view($user, $model);
        } elseif ($user->hasRole(Role::PLAYER)) {
            return $user->getKey() === $model->getKey();
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole([Role::COACH, Role::ADMINISTRATOR]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole(Role::ADMINISTRATOR);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole(Role::ADMINISTRATOR);
    }
}
