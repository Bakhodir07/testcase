<?php

namespace App\Http\Controllers\Api;

use App\Company;
use App\Employee;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{

    public function browse()
    {
        $user = JWTAuth::authenticate();
        if (!check_permission('companies', 'browse', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $companies = Company::select('companies.*', 'users.first_name', 'users.last_name', 'users.middle_name',)
            ->leftJoin('users', 'companies.owner_id', '=', 'users.id')->orderBy('created_at', 'asc')->get();
        return response()->json([
            'success' => 1,
            'data' => $companies
        ], 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'email' => 'required|string|email|max:255',
            'website' => 'required|string|max:255',
            'phone' => 'required|string|max:15|',
            'login' => 'required|string|min:6|max:255|unique:users',
            'password' => 'required|string|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = JWTAuth::authenticate();
        if (!check_permission('companies', 'create', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        // Create Company 
        $company = new Company();
        $company->name = $request->name;
        $company->address = $request->address;
        $company->email = $request->email;
        $company->website = $request->website;
        $company->phone = $request->phone;
        // Create Company Owner
        $company_owner = User::create([
            'login' => $request->login,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'role_id' => 2
        ]);
        $company->owner_id = $company_owner->id;
        $company->save();
        return response()->json([
            'success' => 1,
            'data' => (object) [
                'company' => $company,
                'owner' => $company_owner
            ],
            'msg' => 'Company and its owner successfully created'
        ], 200);
    }

    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = JWTAuth::authenticate();
        if (!check_permission('companies', 'view', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $company = Company::find($request->company_id);
        if (!isset($company->id)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Company not found'
            ], 400);
        }
        if ($user->role_id == 2 &&  $company->owner_id != $user->id) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }

        return response()->json([
            'success' => 1,
            'data' => $company
        ], 200);
    }

    public function edit(Request $request)
    {
        $user = JWTAuth::authenticate();
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        if ($user->role_id == 1 && !$request->company_id) {
            return response()->json([
                'success' => 0,
                'msg' => 'Company id is required for super_admins'
            ], 400);
        }

        if (!check_permission('companies', 'edit', $user)) {
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

        if ($user->role_id == 2 &&  $company->owner_id != $user->id) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $company->name = $request->name ?: $company->name;
        $company->address = $request->address ?: $company->address;
        $company->email = $request->email ?: $company->email;
        $company->website = $request->website ?: $company->website;
        $company->phone = $request->phone ?: $company->phone;

        $company_owner = User::find($company->owner_id);
        $company_owner->first_name = $request->first_name ?: $company_owner->first_name;
        $company_owner->last_name = $request->last_name ?: $company_owner->last_name;
        $company_owner->middle_name = $request->middle_name ?: $company_owner->middle_name;
        if ($request->password) {
            if (strlen($request->password) < 6) {
                return response()->json([
                    'success' => 0,
                    'msg' => 'Password must be consisted at least 6 symbols'
                ], 400);
            }
            $company_owner->password =  Hash::make($request->password);
        }
        $company->save();
        $company_owner->save();
        return response()->json([
            'success' => 1,
            'msg' => 'Succesfully updated',
            'data' => $company
        ], 200);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = JWTAuth::authenticate();
        if (!check_permission('companies', 'delete', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }

        $company = Company::find($request->company_id);
        if (!isset($company->id)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Company not found'
            ], 400);
        }
        $employees = Employee::where('company_id', $company->id)->delete();
        $company->delete();
        return response()->json([
            'success' => 1,
            'msg' => 'Successfully deleted'
        ], 200);
    }
}
