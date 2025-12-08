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

        return Cache::tags(['users'])->remember($key, 5, function () use ($request) {
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

    public function show($id)
    {
        $key = "user_detail_{$id}";

        $user = Cache::tags(['users'])->remember($key, 5, function () use ($id) {
            return User::with(['role', 'counter'])
                ->withTrashed()
                ->findOrFail($id);
        });

        return response()->json([
            'message' => 'User detail retrieved successfully.',
            'data' => $user
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            "name" => "sometimes|string|max:255",
            "email" => "sometimes|email|unique:users,email," . $user->id,
            "role_id" => "sometimes|exists:roles,id",
            "counter_id" => "nullable|exists:counters,id"
        ]);

        if (isset($data['role_id']) && $data['role_id'] == 2) {

            if (!isset($data['counter_id'])) {
                return response()->json([
                    'message' => 'Customer service user must have a counter assigned.'
                ], 422);
            }

            $exists = User::where('role_id', 2)
                ->where('counter_id', $data['counter_id'])
                ->where('id', '!=', $user->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Selected counter is already assigned to another customer service.'
                ], 422);
            }

        }

        if (isset($data['role_id']) && $data['role_id'] != 2) {
            $data['counter_id'] = null;
        }

        $user->update($data);

        $this->clearCache();

        return response()->json([
            "message" => "User updated successfully",
            "data" => $user->load(['role', 'counter']),
        ]);
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

    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        $this->clearCache();

        return response()->json(['message' => 'User permanently deleted']);
    }

    public function trashed()
    {
        $users = User::onlyTrashed()->with('role')->orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'List of archived users retrieved successfully.',
            'data' => $users
        ]);
    }
    public function assignCounter(Request $request, $id)
    {
        $request->validate([
            'counter_id' => 'required|exists:counters,id',
        ]);

        $user = User::findOrFail($id);

        // Validasi: hanya CS yang bisa diberi loket
        if ($user->role_id != 2) {
            return response()->json([
                'message' => 'Only customer service users can be assigned to a counter.'
            ], 422);
        }

        // Validasi: pastikan loket belum dipakai CS lain
        $exists = User::where('role_id', 2)
            ->where('counter_id', $request->counter_id)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Counter already assigned to another customer service.'
            ], 422);
        }

        $user->counter_id = $request->counter_id;
        $user->save();

        $this->clearCache();

        return response()->json([
            'message' => 'Counter assigned successfully.',
            'user' => $user->load('counter'),
        ]);
    }
}
