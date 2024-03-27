<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return response()->json(['user' => $user], 201);
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
        $users = User::with(['roles'])->get();
        return response()->json(['users' => $users], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/user/delete",
     *     summary="delete a user",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="The id of the user you want to delete",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(response="201", description="User deleted successfully"),
     *     @OA\Response(response="404", description="User not found.")
     *     @OA\Response(response="403", description="SuperAdmin user cannot be deleted.")
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

}
