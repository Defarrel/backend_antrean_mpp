<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::orderBy('name')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:permissions,name',
            'description' => 'nullable',
        ]);

        return Permission::create($data);
    }

    public function destroy(Permission $permission)
    {
        $count = $permission->roles()->count();
        if ($count > 0) {
            return response()->json(['message' => 'Permission is used by a role'], 422);
        }

        $permission->delete();
        return response()->json(['message' => 'Permission deleted']);
    }
}
