<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Helpers\ApiResponse;
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

    /**
     * @OA\Delete(
     *     path="/api/invitations/{id}",
     *     summary="Delete an invitation",
     *     description="Deletes an invitation by its ID.",
     *     operationId="deleteInvitation",
     *     tags={"Invitation"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the invitation to delete"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invitation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invitation deleted"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invitation not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function delete(Request $request, UsersInvitation $invitation): JsonResponse
    {
        $invitation->delete();
        return ApiResponse::return200('L\'invitation a été supprimée avec succès');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/confirm-invitation",
     *     summary="Confirm user invitation",
     *     description="Confirms a user's invitation and activates their account.",
     *     operationId="confirmInvitation",
     *     tags={"Invitation"},
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The invitation token provided to the user"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 description="The password for the user",
     *                 example="password123"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 description="The confirmation of the password",
     *                 example="password123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User confirmed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User confirmed"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token or other validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invitation expired or invalid"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User or invitation not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User or invitation not found"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="null"
     *             )
     *         )
     *     )
     * )
     */
    public function confirmInvitation(invitationRequest $request): JsonResponse
    {
        try {
            $payload =  verifyToken($request->token);

            $invitation = UsersInvitation::query()->fieldValue('token', $request->token)->first();

            if (is_null($invitation)) {
                Log::error('Invitation not found');
                return ApiResponse::return400('Invitation expired or invalid');
            }

            $user = User::findOrFail($payload['user_id']);

            if ($user->hasRole(Role::PLAYER)) {
                return ApiResponse::return409('L\'utilisateur a déjà confirmé son invitation');
            }

            $user->status = User::STATUS_ACTIVE;
            $user->password = bcrypt($request->password);
            $user->email_verified_at = Carbon::now();
            $user->coach_id = $invitation->coach_id;
            $success = $user->save();
            $user->assignRole(Role::PLAYER);

            if (!$success) {
                return ApiResponse::return500('ccUne erreur est survenue lors de la confirmation de l\'invitation');
            }

            $invitation->delete();
            return ApiResponse::return200('L\'utilisateur a été confirmé avec succès', $user);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ApiResponse::return500( $e->getMessage());
        }
    }
}
