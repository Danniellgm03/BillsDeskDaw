<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;



class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles',
            'permissions' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'permissions' => $request->permissions,
        ]);

        return response()->json($role, 201);
    }

    public function show(int $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rol no encontrado'], 404);
        }

        return response()->json($role);
    }

    public function update(Request $request, int $id){
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rol no encontrado'], 404);
        }

        $Validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
        ]);

        if ($Validator->fails()) {
            return response()->json(['errors' => $Validator->errors()], 422);
        }

        $role->update($request->only(['name', 'permissions']));

        return response()->json($role);
    }


    public function destroy(int $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['error' => 'Rol no encontrado'], 404);
        }

        $role->delete();

        return response()->json($role);
    }
}
