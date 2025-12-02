<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

class PermissionController extends Controller
{
    private function clearCache()
    {
        Cache::tags(['permissions'])->flush();
    }

    public function index(Request $request)
    {
        $key = 'permissions_index_' . md5(json_encode($request->all()));

        return Cache::tags(['permissions'])->remember($key, 3600, function () {
            return Permission::orderBy('name')->paginate(20);
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:permissions,name',
            'description' => 'nullable',
        ]);

        $permission = Permission::create($data);
        
        $this->clearCache();

        return $permission;
    }

    public function destroy(Permission $permission)
    {
        $count = $permission->roles()->count();
        if ($count > 0) {
            return response()->json(['message' => 'Permission is used by a role'], 422);
        }

        $permission->delete();
        
        $this->clearCache();

        return response()->json(['message' => 'Permission deleted']);
    }
}