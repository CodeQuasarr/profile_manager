<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Http\Controllers\Controller;
use App\Models\Users\Role;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserInvitationController extends Controller
{

    /**
     * @description  Confirmer l'invitation d'un utilisateur
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function confirmInvitation(Request $request): JsonResponse
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
