<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Get logged-in user details",
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function getUserDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $role = $user->roles()->pluck('name');
        $permissions = $role->first() !== null ? $user->roles()->first()->permissions()->pluck('name') : [];

        return response()->json([
            'user' => $user,
            'role' => $role->first() ? $role->first() : null,
            'permissions' => $permissions
        ], 201);
    }


    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     @OA\Response(response="200", description="Success"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function getAllUsers(Request $request): \Illuminate\Http\JsonResponse
    {
        $users = User::all();
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'user' => $user,
                'role' => $user->roles()->pluck('name')->first(),
                'permissions' => $user->roles()->first()->permissions()->pluck('name'),
            ];
        }

        return response()->json(['users' => $data], 200);

    }

    /**
     * @OA\Delete(
     *     path="/api/user/delete",
     *     summary="Delete a user",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="The ID of the user you want to delete",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="User deleted successfully"),
     *     @OA\Response(response="404", description="User not found"),
     *     @OA\Response(response="403", description="SuperAdmin user cannot be deleted")
     * )
     */

    public function delete(Request $request)
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        if ($user->hasRole('SuperAdmin')) {
            return response()->json(['message' => 'SuperAdmin user cannot be deleted.'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.'], 201);
    }


    /**
     * @OA\Put(
     *     path="/api/user/update",
     *     summary="Update a user",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="The ID of the user you want to update",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="User updated successfully"),
     *     @OA\Response(response="404", description="User not found"),
     *     @OA\Response(response="403", description="SuperAdmin user cannot be updated"),
     *     @OA\Response(response="422", description="Role does not exist")
     * )
     */

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|min:4|max:30',
            'email' => [
                'nullable',
                'email',
                'max:255',
                'ends_with:gmail.com',
                Rule::unique('users')->ignore($request->user_id),
            ],
            'role' => 'nullable|string',
        ]);
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('role')) {
            if (!$user->hasRole('SuperAdmin')) {
                if (!$user->changeRole($request->role)) {
                    return response()->json(['message' => "Role does not Exists. "], 403);
                };
            } else {
                return response()->json(['message' => "SuperAdmins role cannot be updated. "], 403);
            }
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully!'
        ], 201);
    }


    /**
     * @OA\Put(
     *     path="/api/user/password/new",
     *     summary="Update user password",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="The ID of the user you want to update",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="The new password ",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="201", description="Password updated successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     * )
     */
    public function updateUserPw(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['message' => 'User not fount.'], 404);
        }
        $request->validate([
            'password' => 'required|string|min:8',
        ]);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User Password updated successfully !'], 201);
    }


    /* @OA\Put(
     *     path="/api/me/password/new",
     *     summary="Update my password",
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         description="The new password ",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="201", description="Password updated successfully"),
     *     @OA\Response(response="403", description="Unauthorized"),
     * )
     */
    public function updateMyPw(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = request()->user();
        $request->validate([
            'password' => 'required|string|min:8',
        ]);
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        return response()->json(['message' => 'Your Password updated successfully !'], 201);
    }
}
