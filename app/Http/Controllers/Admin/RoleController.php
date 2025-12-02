<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    private function clearCache()
    {
        Cache::tags(['roles'])->flush();
    }

    public function index(Request $request)
    {
        $key = 'roles_index_' . md5(json_encode($request->all()));

        return Cache::tags(['roles'])->remember($key, 3600, function () use ($request) {
            $query = Role::query();

            if ($request->archived == 1) {
                $query = Role::onlyTrashed();
            }

            if ($request->search) {
                $q = $request->search;
                $query->where('name', 'ILIKE', "%{$q}%");
            }

            if ($request->sort) {
                $query->orderBy($request->sort, $request->order ?? 'asc');
            }

            return $query->with('permissions')->paginate(20);
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:roles,name',
            'description' => 'nullable',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (!empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        $this->clearCache();

        return $role->load('permissions');
    }

    public function show(Role $role)
    {
        $key = 'role_detail_' . $role->id;

        return Cache::tags(['roles'])->remember($key, 3600, function () use ($role) {
            return $role->load('permissions');
        });
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'description' => 'nullable',
            'permissions' => 'array',
        ]);

        $role->update([
            'description' => $data['description'] ?? $role->description,
        ]);

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        $this->clearCache();

        return $role->load('permissions');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        $this->clearCache();
        return response()->json(['message' => 'Role archived']);
    }

    public function restore($id)
    {
        $role = Role::onlyTrashed()->findOrFail($id);
        $role->restore();
        $this->clearCache();
        return $role->load('permissions');
    }
}