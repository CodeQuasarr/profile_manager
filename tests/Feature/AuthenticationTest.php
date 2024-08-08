<?php

use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);


it("User can authenticate", function () {
    $user = User::factory()->create();

    Passport::keyPath(storage_path('oauth-public.key')); // recuperer les clés de passport
    Passport::actingAs($user); // connecter l'utilisateur

    // Vérifiez que l'utilisateur est authentifié
    $this->assertTrue(auth()->check());
    expect(auth()->user()->getKey())->toBe($user->id);
});

it("User cannot authenticate with an invalid password", function () {
    $user = User::factory()->create(['password' => bcrypt('correct-password')]);

    Passport::keyPath(storage_path('oauth-public.key'));
    $this->post('/api/v1/login',
        [ 'email' => $user->email, 'password' => 'wrong-password' ],
        ['Accept' => 'application/json']
    );

    $this->assertFalse(auth()->check());
});
