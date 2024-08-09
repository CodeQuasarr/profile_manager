<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Http\Controllers\Controller;
use App\Models\Users\Role;
use App\Models\Users\User;
use App\Models\Users\UsersInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsersInvitationController extends Controller
{

    public function delete(Request $request, UsersInvitation $invitation): JsonResponse
    {
        $invitation->delete();
        return response()->json([
            'status' => true,
            'message' => 'Invitation deleted',
            'data' => null
        ]);
    }

    /**
     * @description  Confirmer l'invitation d'un utilisateur
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function confirmInvitation(Request $request): JsonResponse
    {
        try {
            if (!$request->has('token')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token not found',
                    'data' => null
                ]);
            }

            $payload =  verifyToken($request->token);

            $invitation = UsersInvitation::query()->fieldValue('token', $payload['token'])->first();

            if (is_null($invitation->exists())) {
                Log::error('Invitation not found');
                return response()->json([
                    'status' => false,
                    'message' => 'Invitation expirer ou invalide',
                    'data' => null
                ]);
            }

            $user = User::findOrFail($payload['user_id']);

            if ($user->hasRole(Role::PLAYER)) {
                return response()->json([
                    'status' => false,
                    'message' => 'User already confirmed',
                    'data' => null
                ]);
            }

            $user->assignRole(Role::PLAYER);

            return response()->json([
                'status' => true,
                'message' => 'User confirmed',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error',
                'data' => $e->getMessage()
            ]);
        }
    }
}
