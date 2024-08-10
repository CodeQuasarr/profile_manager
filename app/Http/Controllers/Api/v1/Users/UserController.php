<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\Users\Role;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use JetBrains\PhpStorm\NoReturn;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->cannot('viewAny', User::class)) {
            return response()->json([
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized',
                'data' => null
            ]);
        }

        $me = Auth::user();
        $query = QueryBuilder::for(User::class);

        if ($me->hasRole(Role::COACH)) {
            $query = $query = QueryBuilder::for(User::class);
        } elseif ($me->hasRole(Role::PLAYER)) {
            $query = QueryBuilder::for($me->teammates());
        }

        $users = $query
            ->allowedFields('name')
            ->get();
        $users->makeHidden(User::hideFields());

        return response()->json([
            'status' => true,
            'message' => 'Users list',
            'data' => new UserCollection($users)
        ]);

    }

    /**
     * @description List des utilisateurs pour un utilisateur non connecté
     *
     * @return JsonResponse
     */
    public function indexForGuest(): JsonResponse
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters(['first_name', 'email'])
            ->get();

        $users->makeHidden([
            'id',
            'email',
            'password',
            'remember_token',
            'email_verified_at',
            'status',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Liste des utilisateurs',
            'data' => new UserCollection($users)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[NoReturn] public function store(UserRequest $request): JsonResponse
    {
        if ($request->user()->cannot('create', User::class)) {
            return response()->json([
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized',
                'data' => null
            ]);
        }

        // Un coach crée le profil d'un de ses joueurs
        // Un mail sera envoyé à l'utilisateur via un observer et un job pour confirmer son inscription
        $me = auth()->user();
        if ($me->hasRole(Role::COACH)) {
            $userModel = $this->fillModel(new User(), collect($request->all()));
            $success = $userModel->save();
            if (!$success) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not created',
                    'data' => null
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'User created',
                'data' => $userModel
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthorized',
            'data' => null
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => true,
            'message' => "Détais de l'utilisateur",
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if ($request->user()->cannot('view',$user, User::class)) {
            return response()->json([
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized',
                'data' => null
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
