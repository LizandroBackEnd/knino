<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EmployeesController extends Controller
{
    public function addEmployee(Request $request)
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
        return response()->json(['message' => 'Employee added successfully'], 201);
    }

    public function getEmployees()
    {
        $employees = User::all();

        if ($employees->isEmpty()) {
            return response()->json(['message' => 'No employees found'], 404);
        }
        return response()->json($employees, 200);
    }

    public function getEmployeeByEmail($email)
    {
        $employee = User::where('email',$email)->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        return response()->json($employee, 200);

    }

    public function updateEmployeeById(Request $request, $id)
    {
        $employee = User::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
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
            $employee->name = $request->get('name');
        }
        if ($request->has('last_name_primary')) {
            $employee->last_name_primary = $request->get('last_name_primary');
        }
        if ($request->has('last_name_secondary')) {
            $employee->last_name_secondary = $request->get('last_name_secondary');
        }
        if ($request->has('phone')) {
            $employee->phone = $request->get('phone');
        }
        if ($request->has('role')) {
            $employee->role = $request->get('role');
        }
        if ($request->has('email')) {
            $employee->email = $request->get('email');
        }
        if ($request->has('password')) {
            $employee->password = bcrypt($request->get('password'));
        }

        $employee->update();
        return response()->json(['message' => 'Employee updated successfully'], 200);
    }

    public function deleteEmployeeById($id)
    {
        $employee = User::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully'], 200);
    }
}
