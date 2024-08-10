<?php

namespace App\Observers\Users;


use App\Jobs\Mails\SendAccountCreationSuccessNotificationJob;
use App\Models\Users\Role;
use App\Models\Users\User;
use App\Models\Users\UsersInvitation;

class UserObserver
{

    /**
     * Handle the User "saving" event.
     */
    public function saved(User $user)
    {
        // Un coach crée le profil d'un de ses joueurs
        if (auth()->check()) {
            $me = auth()->user();
            if ($me->hasRole(Role::COACH)) {
                $confirmationToken = generateToken($user->getKey());
                $url = route('users.confirm.invitation', ['token' => $confirmationToken]);

                $contents = [
                    'club' => 'LDLC ASVEL', // plus tard :  $me->club->name
                    'name' => $me->getName(),
                    'url' => $url,
                ];

                SendAccountCreationSuccessNotificationJob::dispatch(
                    $user->email,
                    'mails.confirmationInvitation',
                    "Invitation à rejoindre l'équipe en ligne sur Basket Fusion",
                    $contents
                );

                UsersInvitation::create([
                    'coach_id' => $me->getKey(),
                    'email' => $user->email,
                    'token' => $confirmationToken,
                ]);
            }
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Envoyer un email de bienvenue lorsqu'un utilisateur (coach) est créé.

        if (!auth()->check()) {
            $contents = [
                'name' => $user->getName(),
            ];
            SendAccountCreationSuccessNotificationJob::dispatch($user->email, 'mails.successCreation', 'Bienvenue sur la plateforme Basket Fusion', $contents);
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
