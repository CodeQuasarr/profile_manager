<?php

namespace App\Http\Controllers\Api\v1\Users;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Lib\TheCurrent;
use App\Models\Users\Role;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserController extends ApiController
{
    /**
     * Display a listing of the users.
     *
     * @param Request $request
     * @return UserCollection|JsonResponse
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
        } elseif ($me->hasRole(Role::ADMINiSTRATOR)) {
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
     * @description User list for an offline user
     *
     * @return UserCollection
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
     * Store a newly created resource in storage.
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
        $me = TheCurrent::user();
        $fields = $this->getModelFields(new User(), collect($request->all()));
        $user = User::create($fields->toArray());

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not created',
                'data' => null
            ]);
        }

        $me->hasRole(Role::ADMINISTRATOR) ?
            $user->status = User::STATUS_ACTIVE :
            $user->coach_id = $me->getKey();
         $user->save();

        return new UserResource($user->makeHidden(User::hideFields()));
    }

    /**
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
