<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\invitationRequest;
use App\Models\Users\Role;
use App\Models\Users\User;
use App\Models\Users\UsersInvitation;
use Carbon\Carbon;
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
     *
     * @param invitationRequest $request
     * @return JsonResponse
     */
    public function confirmInvitation(invitationRequest $request): JsonResponse
    {
        try {
            $payload =  verifyToken($request->token);

            $invitation = UsersInvitation::query()->fieldValue('token', $request->token)->first();

            if (is_null($invitation)) {
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

            $user->status = User::STATUS_ACTIVE;
            $user->password = bcrypt($request->password);
            $user->email_verified_at = Carbon::now();
            $user->coach_id = $invitation->coach_id;
            $success = $user->save();
            $user->assignRole(Role::PLAYER);

            if (!$success) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not confirmed',
                    'data' => null
                ]);
            }

            $invitation->delete();

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
