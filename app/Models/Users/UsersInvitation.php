<?php

namespace App\Models\Users;

use App\Models\Traits\GlobalTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="UsersInvitation",
 *     type="object",
 *     title="Users Invitation",
 *     description="Invitation model for users",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The ID of the invitation",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="coach_id",
 *         type="integer",
 *         description="The ID of the coach who sent the invitation",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="The email address of the invitee",
 *         example="invitee@example.com"
 *     ),
 *     @OA\Property(
 *         property="token",
 *         type="string",
 *         description="The token used for invitation confirmation",
 *         example="abcd1234"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="The date and time the invitation was created",
 *         example="2024-08-12T09:07:23.624Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="The date and time the invitation was last updated",
 *         example="2024-08-12T09:07:23.624Z"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="The date and time the invitation was soft deleted",
 *         example="2024-08-12T09:07:23.624Z"
 *     )
 * )
 */
class UsersInvitation extends Model
{
    use HasFactory, SoftDeletes, GlobalTrait;

    protected $fillable = [
        'coach_id',
        'email',
        'token'
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }
}
