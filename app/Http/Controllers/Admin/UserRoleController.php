<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserRoleController extends Controller
{
    public function assign(Request $request, $id)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);

        $user = User::findOrFail($id);
        $user->role_id = $request->role_id;
        $user->save();

        return $user->load('role');
    }
}
