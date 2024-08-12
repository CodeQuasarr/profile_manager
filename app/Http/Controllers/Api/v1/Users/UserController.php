<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Lib\TheCurrent;
use App\Models\Users\Role;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Get a list of users",
     *     description="Returns a list of users. Supports filtering by name, email, and status.",
     *     operationId="getUsers",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter users by first name"
     *     ),
     *     @OA\Parameter(
     *         name="last_name",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter users by last name"
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter users by email"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"}),
     *         description="Filter users by status ('active' or 'inactive')"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request): UserCollection | JsonResponse
    {
        if (TheCurrent::user()->cannot('viewAny', User::class)) {
            return response()->json([
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized',
                'data' => null
            ]);
        }

        $me = TheCurrent::user(); // logged user
        $query = null;

        if ($me->hasRole(Role::COACH)) {
            $query = QueryBuilder::for( $me->players() );
        } elseif ($me->hasRole(Role::PLAYER)) {
            $query = QueryBuilder::for( $me->teammates() );
        } elseif ($me->hasRole(Role::ADMINISTRATOR)) {
            $query = QueryBuilder::for( User::class );
        }

        $users = $query
            ->allowedFilters(['first_name', 'email'])
            ->paginate(5)
            ->appends(request()->query());

        // Hide some fields
        $users->map(function ($user) {
            $user->makeHidden(User::hideFields());
            return $user;
        });

        return new UserCollection($users);

    }

    /**
     * @OA\Get(
     *     path="/api/v1/users-guest",
     *     summary="Get a list of users",
     *     description="Returns a list of users. Supports filtering by name, email, and status.",
     *     operationId="getGuestUsers",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter users by first name"
     *     ),
     *     @OA\Parameter(
     *         name="last_name",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter users by last name"
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter users by email"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"}),
     *         description="Filter users by status ('active' or 'inactive')"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function indexForGuest(): UserCollection
    {
        $query = QueryBuilder::for( User::query()->loggedUserTeam() );
        $users = $query
            ->allowedFilters(['first_name', 'email'])
            ->paginate(5)
            ->appends(request()->query());

        $users->map(function ($user) {
            $user->makeHidden([
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
            return $user;
        });

        return new UserCollection($users);
    }

    /**
     * @OA\Schema(
     *     schema="UserCreateRequest",
     *     type="object",
     *     title="User Create Request",
     *     description="Schema for creating a new user",
     *     @OA\Property(
     *         property="first_name",
     *         type="string",
     *         description="The first name of the user",
     *         example="John"
     *     ),
     *     @OA\Property(
     *         property="last_name",
     *         type="string",
     *         description="The last name of the user",
     *         example="Doe"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         format="email",
     *         description="The email address of the user",
     *         example="john.doe@example.com"
     *     ),
     *     @OA\Property(
     *         property="password",
     *         type="string",
     *         description="The password for the user",
     *         example="password123"
     *     )
     * )
     */
    public function store(UserRequest $request): UserResource | JsonResponse
    {
        if (TheCurrent::user()->cannot('create', User::class)) {
            return response()->json([
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized',
                'data' => null
            ]);
        }

        // A coach creates a profile for one of his players
        // An e-mail will be sent to the user via an observer and a job to confirm registration.
        $currentUser = TheCurrent::user();
        $fields = $this->getModelFields(new User(), collect($request->all()));
        $user = User::create($fields->toArray());

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not created',
                'data' => null
            ]);
        }

        if ($currentUser->hasRole(Role::ADMINISTRATOR)) {
            $user->email_verified_at = Carbon::now();
            DB::table('model_has_roles')->insert([
                'role_id' => 1,
                'model_id' => $user->getKey(),
                'model_type' => User::class
            ]);
        } else {
            $user->coach_id = $currentUser->getKey();
        }

         $user->save();

        return new UserResource($user->makeHidden(User::hideFields()));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     summary="Get a specific user",
     *     description="Returns the details of a specific user by ID.",
     *     operationId="getUser",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the user to retrieve"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(User $user)
    {
        if (TheCurrent::user()->cannot('view', $user)) {
            return response()->json([
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized',
                'data' => null
            ]);
        }
        new UserResource($user->makeHidden(User::hideFields()));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     summary="Update a specific user",
     *     description="Updates the details of a specific user by ID.",
     *     operationId="updateUser",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the user to update"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(Request $request, User $user)
    {
        if (TheCurrent::user()->cannot('view',$user)) {
            return response()->json([
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized',
                'data' => null
            ]);
        }

        $fields = $this->getModelFields($user, collect($request->all()));
        $user->update($fields->toArray());
        return new UserResource($user->makeHidden(User::hideFields()));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     summary="Delete a specific user",
     *     description="Deletes a specific user by ID.",
     *     operationId="softDeleteUser",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the user to delete"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy(User $user)
    {
        if (TheCurrent::user()->cannot('delete', $user)) {
            return response()->json([
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized',
                'data' => null
            ]);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'code' => ResponseAlias::HTTP_OK,
            'message' => 'L\'utilisateur a été supprimé',
            'data' => null
        ]);
    }

    /**
     * @OA\Delete (
     *     path="/api/v1/users/{id}/forces",
     *     summary="Force delete a user",
     *     description="Permanently deletes a user from the database.",
     *     operationId="forceDelete",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the user to delete"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function forceDelete(User $user): JsonResponse
    {
        if (TheCurrent::user()->cannot('forceDelete', $user)) {
            return response()->json([
                'status' => false,
                'code' => ResponseAlias::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized',
                'data' => null
            ]);
        }

        $user->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'L\'utilisateur a été supprimé définitivement',
            'data' => null
        ]);
    }
}
