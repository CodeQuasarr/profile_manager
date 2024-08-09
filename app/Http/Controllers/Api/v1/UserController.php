<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UserRequest;
use App\Models\Users\Role;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'List of users',
            'data' => User::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request): JsonResponse
    {
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * @throws \Exception
     */
    public function confirmInvitation(Request $request)
    {
        if (!$request->has('token')) {
            return response()->json([
                'status' => false,
                'message' => 'Token not found',
                'data' => null
            ]);
        }
        $payload =  verifyToken($request->token);
        $user = User::findOrFail($payload['user_id']);

        $user->assignRole(Role::PLAYER);

        return response()->json([
            'status' => true,
            'message' => 'User confirmed',
            'data' => $user
        ]);
    }
}
