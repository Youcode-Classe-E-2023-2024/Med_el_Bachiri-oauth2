<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/role/create",
     *     summary="Create new role.",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Role name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *         name="permissions",
     *         in="query",
     *         description="Array of permissions to assign to the new role",
     *         required=true,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="integer")
     *         )
     *     ),
     *     @OA\Response(response="201", description="Role created successfully"),
     *     @OA\Response(response="403", description="Validation errors"),
     * )
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:20|unique:roles',
            'permissions.*' => 'required|integer|exists:permissions,id',
            ]);
        $role = Role::create([
            'name' => $request->name
        ]);
        $role->assignPermissions($request->permissions);
        return response()->json(['message' => 'Role created successfully.']);
    }

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Get all roles.",
     *     @OA\Response(response="201", description="Role created successfully"),
     *     @OA\Response(response="403", description="Validation errors"),
     * )
     */
    public function showAll()
    {
        $roles = Role::with('permissions:name')->get();
        return response()->json(['roles' => $roles]);
    }

    /**
     * @OA\Put(
     *     path="/api/role/update/{role_id}",
     *     summary="Update an existing role.",
     *     @OA\Parameter(
     *         name="role_id",
     *         in="path",
     *         description="ID of the role to be updated",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="New role name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="permissions",
     *         in="query",
     *         description="Array of the new permissions",
     *         required=true,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="integer")
     *         ),
     *         style="form"
     *     ),
     *     @OA\Response(response="200", description="Role updated successfully"),
     *     @OA\Response(response="403", description="Validation errors"),
     * )
     */
    public function update(Request $request, string $id)
    {
        if (!Role::find($id)) {
            return response()->json(['message' => 'Role now found.'], 403);
        }
        $request->validate([
            'name' => 'nullable|min:3|max:20|unique:roles,name,' . $id,
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::findOrFail($id);

        if ($request->filled('name')) {
            $role->name = $request->name;
            $role->save();
        }

        if ($request->filled('permissions')) {
            $role->updatePermissions($request->permissions);
        }

        return response()->json(['message' => 'Role updated successfully.']);
    }



    /**
     * @OA\Delete (
     *     path="/api/role/delete",
     *     summary="Delete role.",
     *     @OA\Parameter(
     *         name="role_id",
     *         in="query",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="201", description="Role deleted successfully"),
     *     @OA\Response(response="403", description="Cannot delete SuperAdmin role."),
     * )
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Role not found.'], 403);
        }
        if ($role->id === 1) {
            return response()->json(['message' => 'Cannot delete SuperAdmin role.'], 403);
        }
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully.'], 201);
    }
}
