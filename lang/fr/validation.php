<?php

return [
    'weight' => [
        'required' => 'Le poids est requis',
        'numeric' => 'Le poids doit être un nombre',
        'min' => 'Le poids doit être supérieur à :min',
    ],
    'height' => [
        'required' => 'La taille est requise',
        'numeric' => 'La taille doit être un nombre',
        'min' => 'La taille doit être supérieure à :min',
    ],
    'game_position' => [
        'required' => 'Le poste est requis',
        'string' => 'Le poste doit être une chaîne de caractères',
        'in' => 'Le poste doit être l\'un des suivants : SG, PG, SF, PF, C',
    ],
];
