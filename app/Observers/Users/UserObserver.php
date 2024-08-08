<?php

namespace App\Observers\Users;


use App\Jobs\Mails\SendAccountCreationSuccessNotificationJob;
use App\Models\Users\Role;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserObserver
{

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Envoyer un email de bienvenue lorsqu'un utilisateur est créé avec le role de coach.
        SendAccountCreationSuccessNotificationJob::dispatch($user->email, $user->getName());
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
