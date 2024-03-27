<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        $user = Auth::user();
        $role = Role::where('name', $user->roles[0]->name)->get()->first();
        $result = $role->hasPermissionTo($permission);
        if ($result) {
            return $next($request);
        } else {
            return \response()->json(['message' => 'Unauthorized']);
        }
    }
}
