<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');

        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->get(['id', 'name', 'email', 'role']); // ambil field penting saja

        return response()->json([
            'data' => $users
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id, ['id', 'name', 'email', 'role', 'created_at', 'updated_at']);

        return response()->json([
            'data' => $user
        ]);
    }
}
