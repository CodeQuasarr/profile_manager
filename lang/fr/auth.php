<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
    'required' => 'Le :attribute est requis',
    'name' => [
        'string' => 'Le :attribute doit être une chaîne de caractères',
        'min' => 'Le :attribute doit contenir au moins :min caractères',
        'max' => 'Le :attribute ne doit pas dépasser :max caractères',
    ],
    'email' => [
        'email' => 'L\'adresse e-mail doit être une adresse e-mail valide',
        'unique' => 'L\'adresse e-mail est déjà utilisée',
    ],
    'password' => [
        'min' => 'Le mot de passe doit contenir au moins :min caractères',
        'confirmed' => 'Les mots de passe ne correspondent pas',
        'regex' => 'Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un caractère spécial et un chiffre',
    ],
    'throttle' => 'Trop de tentatives de connexion. Veuillez réessayer dans :seconds secondes.',
    'success_register' => 'Utilisateur créé avec succès',

];
