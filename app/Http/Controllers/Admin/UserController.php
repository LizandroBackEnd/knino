<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function addUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name_primary' => 'required|string|max:255',
            'last_name_secondary' => 'nullable|string|max:255',
            'phone' => 'required|string|max:10',
            'role' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        User::create([
            'name' => $request->name,
            'last_name_primary' => $request->last_name_primary,
            'last_name_secondary' => $request->last_name_secondary,
            'phone' => $request->phone,
            'role' => $request->role,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        return response()->json(['message' => 'User added successfully'], 201);
    }

    public function getUsers()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return response()->json(['message' => 'user not found'], 404);
        }
        return response()->json($users, 200);
    }

    public function getUserByEmail($email)
    {
        $user = User::where('email',$email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);

    }

    public function updateUserById(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'last_name_primary' => 'sometimes|required|string|max:255',
            'last_name_secondary' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|required|string|max:10',
            'role' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $user->name = $request->get('name');
        }
        if ($request->has('last_name_primary')) {
            $user->last_name_primary = $request->get('last_name_primary'); 
        }
        if ($request->has('last_name_secondary')) {
            $user->last_name_secondary = $request->get('last_name_secondary');
        }
        if ($request->has('phone')) {
            $user->phone = $request->get('phone');
        }
        if ($request->has('role')) {
            $user->role = $request->get('role');
        }
        if ($request->has('email')) {
            $user->email = $request->get('email');
        }
        if ($request->has('password')) {
            $user->password = bcrypt($request->get('password'));
        }

        $user->update();
        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function deleteUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
