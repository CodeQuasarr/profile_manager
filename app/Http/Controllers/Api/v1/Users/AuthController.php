<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Lib\TheCurrent;
use App\Models\Users\Role;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info (
 *     title="API de gestion des utilisateurs",
 *     version="1.0.0",
 *     description="API de gestion des utilisateurs"
 * )
 */
class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Authentifie l'utilisateur",
     *     description="Authentifie l'utilisateur et génère un jeton d'accès.",
     *     operationId="loginUser",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="first_coach@team.eu"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="L'utilisateur s'est connecté avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User logged in successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="token", type="string", example="your_access_token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = Auth::user();
        $token = $user->createToken('access_token')->accessToken;

        return ApiResponse::return200(
            'Authentification réussie. Bienvenue !',
            ['token' => $token]
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     summary="User profile",
     *     description="User profile",
     *     operationId="show",
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthennticated"
     *     )
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        $user = TheCurrent::user();
        return ApiResponse::return200('Profile utilisateur', $user);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Crée un nouvel utilisateur",
     *     description="Cette méthode crée un coach par défaut lors de l'inscription.",
     *     operationId="registerUser",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name","last_name","email","password"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="yourpassword"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="yourpasswordconfirmation"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Votre compte a été créé avec succès; Un email de confirmation vous a été envoyé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Votre compte a été créé avec succès; Un email de confirmation vous a été envoyé"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Une erreur s'est produite, Veuillez réessayer",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Une erreur s'est produite, Veuillez réessayer"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *         ),
     *     ),
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = new User([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'status' => User::STATUS_ACTIVE,
        ]);

        $user->email_verified_at = Carbon::now();
        $succes = $user->save();
        if (!$succes) {
            return ApiResponse::return500('Une erreur s\'est produite, Veuillez réessayer');
        }
        $user->assignRole(Role::COACH);

        return ApiResponse::return200('Votre compte a été créé avec succès; Un email de confirmation vous a été envoyé');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     summary="Déconnecte l'utilisateur",
     *     description="Révoque le jeton d'accès de l'utilisateur connecté.",
     *     security={{"bearer":{}}},
     *     operationId="logoutUser",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="your_access_token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }

}
