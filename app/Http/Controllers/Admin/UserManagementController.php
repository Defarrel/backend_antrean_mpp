<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserManagementController extends Controller
{
    private function clearCache()
    {
        Cache::tags(['users'])->flush();
    }

    public function index(Request $request)
    {
        $key = 'users_index_' . md5(json_encode($request->all()));

        return Cache::tags(['users'])->remember($key, 3600, function () use ($request) {
            $query = User::with('role');

            if ($request->archived == 1) {
                $query->onlyTrashed();
            }

            if ($request->search) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%$search%")
                        ->orWhere('email', 'ILIKE', "%$search%");
                });
            }

            if ($request->sort) {
                $query->orderBy($request->sort, $request->order ?? 'asc');
            }

            return $query->paginate(20);
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users,email",
            "password" => "required",
            "role_id" => "required|exists:roles,id"
        ]);

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        $this->clearCache();

        return $user;
    }

    public function updateRole(Request $request, $id)
    {
        $data = $request->validate([
            "role_id" => "required|exists:roles,id"
        ]);

        $user = User::findOrFail($id);
        $user->role_id = $data['role_id'];
        $user->save();

        $this->clearCache();

        return response()->json([
            "message" => "Role updated",
            "user" => $user->load('role'),
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        $this->clearCache();

        return response()->json(['message' => 'User archived']);
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        $this->clearCache();

        return $user->load('role');
    }
}