<?php

namespace App\Observers\Users;


use App\Jobs\Mails\SendAccountCreationSuccessNotificationJob;
use App\Lib\TheCurrent;
use App\Models\Users\User;
use App\Models\Users\UsersInvitation;

class UserObserver
{

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Email when a user (coach) is created
        if (!TheCurrent::user()) {
            $contents = [
                'name' => $user->getName(),
            ];
            SendAccountCreationSuccessNotificationJob::dispatch($user->email, 'mails.successCreation', 'Bienvenue sur la plateforme Basket Fusion', $contents);
        }

        // Email to confirm
        if (!TheCurrent::user()) {
            $contents = [
                'name' => $user->getName(),
            ];
            SendAccountCreationSuccessNotificationJob::dispatch(
                $user->email,
                'mails.successCreation',
                'Bienvenue sur la plateforme Basket Fusion',
                $contents
            );
        }

        // Email the user when a coach creates an account for him.
        if (TheCurrent::user() && TheCurrent::user()->isCoach()) {
            $confirmationToken = generateToken($user->getKey());
            $url = route('users.confirm.invitation', ['token' => urlencode($confirmationToken)]);

            $contents = [
                'club' => 'LDLC ASVEL', //TODO plus tard :  $me->club->name
                'name' => TheCurrent::user()->getName(),
                'url' => $url,
            ];

            SendAccountCreationSuccessNotificationJob::dispatch(
                $user->email,
                'mails.confirmationInvitation',
                "Invitation à rejoindre l'équipe en ligne sur Basket Fusion",
                $contents
            );

            UsersInvitation::create([
                'coach_id' => TheCurrent::user()->getKey(),
                'email' => $user->email,
                'token' => $confirmationToken,
            ]);
        }
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
