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
        return response()->json([
            'status' => true,
            'message' => 'Invitation deleted',
            'data' => null
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/invitations/confirm",
     *     summary="Confirm user invitation",
     *     description="Confirms a user's invitation and activates their account.",
     *     operationId="confirmInvitation",
     *     tags={"Invitation"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(
     *                      property="token",
     *                      type="string",
     *                      description="The token for confirming the invitation",
     *                      example="abcd1234"
     *                  ),
     *                  @OA\Property(
     *                       property="password",
     *                       type="string",
     *                       description="The password for the user",
     *                       example="password123"
     *                   ),
     *                  @OA\Property(
     *                       property="password_confirmation",
     *                       type="string",
     *                       description="The confirmation of the password",
     *                       example="password123"
     *                   )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User confirmed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(
     *                     property="status",
     *                     type="boolean",
     *                     example=true
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="User confirmed"
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     ref="#/components/schemas/User"
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token or other validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(
     *                     property="status",
     *                     type="boolean",
     *                     example=false
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Invitation expired or invalid"
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="null"
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User or invitation not found",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(
     *                     property="status",
     *                     type="boolean",
     *                     example=false
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="User or invitation not found"
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="null"
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(
     *                     property="status",
     *                     type="boolean",
     *                     example=false
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="Unauthorized"
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="null"
     *                 )
     *             }
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
