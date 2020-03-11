<?php

namespace App\Http\Controllers\Api;

use App\Company;
use App\Employee;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class EmployeeController extends Controller
{
    public function browse()
    {
        $user = JWTAuth::authenticate();
        if (!check_permission('employees', 'browse', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        if ($user->role_id == 1) {
            $employee = Employee::all();
        } else {
            $employee = Employee::where('company_id', $user->company_id)->get();
        }
        return response()->json([
            'success' => 1,
            'data' => $employee
        ], 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'position_id' => 'required|int',
            'password_serial' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = JWTAuth::authenticate();
        if ($user->role_id == 1 && !$request->company_id) {
            return response()->json([
                'success' => 0,
                'msg' => 'Company id is required for super_admins'
            ], 400);
        }
        if (!check_permission('employees', 'create', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $c_id = $user->role_id == 1 ? $request->company_id : $user->company_id;
        $company = Company::find($c_id);
        if (!isset($company->id)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Company not found'
            ], 400);
        }
        // Create Company Employee
        $company_employee = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'role_id' => 3
        ]);
        // Create Company 
        $employee = new Employee();
        $employee->user_id = $company_employee->id;
        $employee->company_id = $user->role_id == 1 ? $request->company_id : $user->company_id;
        $employee->address = $request->address;
        $employee->password_serial = $request->password_serial;
        $employee->position_id = $request->position_id;
        $employee->phone = $request->phone;
        $employee->save();
        return response()->json([
            'success' => 1,
            'msg' => 'Employee successfully created'
        ], 200);
    }

    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = JWTAuth::authenticate();
        if (!check_permission('employees', 'view', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $employee = Employee::find($request->employee_id);
        if (!isset($employee->id)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Employee not found'
            ], 400);
        }
        if ($user->role_id == 2 &&  $employee->company_id != $user->company_id) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }

        return response()->json([
            'success' => 1,
            'data' => $employee
        ], 200);
    }

    public function edit(Request $request)
    {
        $user = JWTAuth::authenticate();
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        if (!check_permission('employees', 'edit', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $employee = Employee::find($request->employee_id);
        if (!isset($employee->id)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Employee not found'
            ], 400);
        }

        if ($user->role_id == 2 &&  $employee->company_id != $user->company_id) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }

        $employee->password_serial = $request->password_serial ?: $employee->password_serial;
        $employee->address = $request->address ?: $employee->address;
        $employee->position_id = $request->position_id ?: $employee->position_id;
        $employee->phone = $request->phone ?: $employee->phone;
        $employee->save();

        $user =  User::find($employee->user_id);
        $user->first_name = $request->first_name ?: $user->first_name;
        $user->last_name = $request->last_name ?: $user->last_name;
        $user->middle_name = $request->middle_name ?: $user->middle_name;
        $user->save();
        return response()->json([
            'success' => 1,
            'msg' => 'Succesfully updated',
        ], 200);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = JWTAuth::authenticate();
        if (!check_permission('employees', 'delete', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $employee = Employee::find($request->employee_id);
        if (!isset($employee->id)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Employee not found'
            ], 400);
        }
        if ($user->role_id == 2 &&  $employee->company_id != $user->company_id) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $user = User::find($employee->user_id);
        $user->delete();
        $employee->delete();
        return response()->json([
            'success' => 1,
            'msg' => 'Successfully deleted'
        ], 200);
    }
}
