<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Users\Role;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    /**
     * @description Authentifie l'utilisateur et génère un jeton d'accès.
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = Auth::user();
        $token = $user->createToken('access_token')->accessToken;

        return response()->json([
            'status' => true,
            'message' => 'User logged in successfully',
            'date' => [],
            'token' => $token
        ]);
    }

    /**
     * @description Récupère le profil de l'utilisateur connecté.
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $user = Auth::user();
        return response()->json([
            'status' => true,
            'message' => 'User profile',
            'data' => $user
        ]);
    }

    /**
     * @description Crée un nouvel utilisateur.
     *
     * Cette méthode Va créer un coach par défaut lors de l'inscription.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {

        $user = new User([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $succes = $user->save();
        if (!$succes) {
            // return error
            return response()->json([
                'status' => false,
                'message' => 'Une erreur s\'est produite, Veuillez réessayer',
                'data' => []
            ], 500);
        }
        $user->assignRole(Role::COACH);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => []
        ], 201);
    }

    /**
     * @description Déconnecte l'utilisateur (révoque le jeton d'accès).
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }

}
