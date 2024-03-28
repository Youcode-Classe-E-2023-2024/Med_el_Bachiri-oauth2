<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function showAll()
    {
        return response()->json([Permission::all()]);
    }
}
