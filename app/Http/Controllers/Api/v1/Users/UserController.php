<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Helpers\ApiResponse;
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

class UserController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="Get a list of users",
     *     description="Returns a list of users. Supports filtering by name, email, and status.",
     *     operationId="getUsers",
     *     tags={"User"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="filter[first_name]",
     *         in="query",
     *         example="emelie",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filtrer les utilisateurs par prénom"
     *     ),
     *     @OA\Parameter(
     *         name="filter[lst_name]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filtrer les utilisateurs par nom"
     *     ),
     *     @OA\Parameter(
     *          name="filter[status]",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="inumber", enum={"--", "active", "inactive", "Supprimer"}),
     *          description="Filter users by status ('active' or 'inactive')"
     *      ),
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
    public function index(Request $request): UserCollection|JsonResponse
    {
        TheCurrent::user()->cannot('viewAny', User::class);

        $me = TheCurrent::user(); // logged user
        $query = null;
        if ($me->hasRole(Role::COACH)) {
            $query = QueryBuilder::for($me->players());
        } elseif ($me->hasRole(Role::PLAYER)) {
            $query = QueryBuilder::for($me->teammates());
        } elseif ($me->hasRole(Role::ADMINISTRATOR)) {
            $query = QueryBuilder::for(User::class);
        }

        $users = $query
            ->allowedFilters(['first_name', 'last_name', 'status'])
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
     *          name="filter[first_name]",
     *          in="query",
     *          example="emelie",
     *          required=false,
     *          @OA\Schema(type="string"),
     *          description="Filtrer les utilisateurs par prénom"
     *      ),
     *      @OA\Parameter(
     *          name="filter[last_name]",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="string"),
     *          description="Filtrer les utilisateurs par nom"
     *      ),
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
    public function indexForGuest(Request $request): UserCollection
    {
        $query = QueryBuilder::for(User::query()->loggedUserTeam());
        $users = $query
            ->allowedFilters(['first_name', 'last_name'])
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
     * @OA\Post(
     *     path="/api/v1/users",
     *     summary="Create a new user",
     *     description="Creates a new user. Required fields depend on the role of the logged-in user.",
     *     operationId="createUser",
     *     tags={"User"},
     *     security={{"bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string",
     *                     example="John"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string",
     *                     example="Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     example="john.doe@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="weight",
     *                     type="number",
     *                     format="float",
     *                     example=75
     *                 ),
     *                 @OA\Property(
     *                     property="height",
     *                     type="number",
     *                     format="float",
     *                     example=180
     *                 ),
     *                 @OA\Property(
     *                     property="game_position",
     *                     type="string",
     *                     example="Forward"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional image file"
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(UserRequest $request): UserResource|JsonResponse
    {
        if (TheCurrent::user()->cannot('create', User::class)) {
            return ApiResponse::return401();
        }

        // A coach creates a profile for one of his players
        // An e-mail will be sent to the user via an observer and a job to confirm registration.
        $currentUser = TheCurrent::user();
        $fields = $this->getModelFields(new User(), collect($request->all()));

        // Traitement de l'image
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('images', $imageName, 'public');
            $fields->put('image', $imageName);
        }
        $fields->put('password', bcrypt('password'));
        $user = User::create($fields->toArray());

        if (!$user) {
            return ApiResponse::return500();
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
     *      security={{"bearer":{}}},
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
            return ApiResponse::return401('Action non autorisée');
        }
        new UserResource($user->makeHidden(User::hideFields()));
    }

    /**
     * @OA\Put(
     *      path="/api/v1/users/{id}",
     *      summary="Update a specific user",
     *      description="Updates the details of a specific user by ID. Allows the optional upload of an image.",
     *      security={{"bearer":{}}},
     *      operationId="updateUser",
     *      tags={"User"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer"),
     *              description="The ID of the user to update"
     *          ),
     *          @OA\RequestBody(
     *              required=true,
     *              @OA\MediaType(
     *                  mediaType="multipart/form-data",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="first_name",
     *                          type="string",
     *                          example="John"
     *                      ),
     *                      @OA\Property(
     *                          property="last_name",
     *                          type="string",
     *                          example="Doe"
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          type="string",
     *                          example="john.doe@example.com"
     *                      ),
     *                      @OA\Property(
     *                          property="image",
     *                          type="string",
     *                          format="binary",
     *                          description="Optional image file"
     *                      )
     *                  )
     *              )
     *          ),
     *          @OA\Response(
     *              response=200,
     *              description="User updated successfully",
     *              @OA\JsonContent(ref="#/components/schemas/User")
     *          ),
     *          @OA\Response(
     *          response=404,
     *          description="User not found"
     *          ),
     *          @OA\Response(
     *              response=401,
     *              description="Unauthorized"
     *          )
     * )
     */
    public function update(Request $request, User $user): UserResource|JsonResponse
    {
        if (TheCurrent::user()->cannot('view', $user)) {
            return ApiResponse::return401('Action non autorisée');
        }

        $fields = $this->getModelFields($user, collect($request->all()));
        // Handle image update
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('images', $imageName, 'public');
            $fields->put('image_name', $imageName);
        }
        $user->update($fields->toArray());
        return new UserResource($user->makeHidden(User::hideFields()));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     summary="Delete a specific user",
     *     description="Deletes a specific user by ID.",
     *      security={{"bearer":{}}},
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
    public function destroy(User $user): JsonResponse
    {
        if (TheCurrent::user()->cannot('delete', $user)) {
            return ApiResponse::return401('Action non autorisée');
        }

        if ($user->image) {
            $path = public_path('storage/images/' . $user->image);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $user->delete();
        return ApiResponse::return200('L\'utilisateur a été supprimé avec succès');
    }

    /**
     * @OA\Delete (
     *     path="/api/v1/users/{id}/force",
     *     summary="Force delete a user",
     *     description="Permanently deletes a user from the database.",
     *     security={{"bearer":{}}},
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
            return ApiResponse::return401('Action non autorisée');
        }

        if ($user->image) {
            $path = public_path('storage/images/' . $user->image);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $user->forceDelete();
        return ApiResponse::return200('L\'utilisateur a été supprimé avec succès');
    }
}
