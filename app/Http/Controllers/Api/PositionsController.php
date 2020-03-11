<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Position;
use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PositionsController extends Controller
{
    public function browse()
    {
        $user = JWTAuth::authenticate();
        if (!check_permission('positions', 'browse', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $positions = Position::all();
        return response()->json([
            'success' => 1,
            'data' => $positions
        ], 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = JWTAuth::authenticate();
        if (!check_permission('positions', 'create', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $position =  new Position();
        $position->name = $request->name;
        $position->save();
        return response()->json([
            'success' => 1,
            'msg' => 'Position successfully created'
        ], 200);
    }

    public function view(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = JWTAuth::authenticate();
        if (!check_permission('positions', 'view', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $position = Position::find($request->position_id);
        if (!isset($position->id)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Position not found'
            ], 400);
        }

        return response()->json([
            'success' => 1,
            'data' => $position
        ], 200);
    }

    public function edit(Request $request)
    {
        $user = JWTAuth::authenticate();
        $validator = Validator::make($request->all(), [
            'position_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        if (!check_permission('positions', 'edit', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $position = Position::find($request->position_id);
        if (!isset($position->id)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Positon not found'
            ], 400);
        }
        $position->name = $request->name ?: $position->name;
        $position->save();
        return response()->json([
            'success' => 1,
            'msg' => 'Succesfully updated',
        ], 200);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = JWTAuth::authenticate();
        if (!check_permission('positions', 'delete', $user)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Access denied'
            ], 400);
        }
        $position = Position::find($request->position_id);
        if (!isset($position->id)) {
            return response()->json([
                'success' => 0,
                'msg' => 'Position not found'
            ], 400);
        }
        $position->delete();
        return response()->json([
            'success' => 1,
            'msg' => 'Successfully deleted'
        ], 200);
    }
}
