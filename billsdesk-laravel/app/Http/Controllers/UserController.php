<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    //create index with pagination and search
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 5);
        $search = $request->input('search', '');

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $company = $user->company;
        $users = $company->users()
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                }
            })
            ->paginate($perPage);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

         if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $company = $user->company;

        // Crear el nuevo usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'company_id' => $company->id, // Asignar la empresa del usuario autenticado
        ]);

        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::find($id);

        $Authuser = Auth::user();

        if (!$Authuser) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if ($Authuser->company_id !== $user->company_id) {
            return response()->json(['error' => 'No tienes permisos para ver este usuario'], 403);
        }

        return response()->json($user);
    }

    // Actualizar un usuario
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        $Authuser = Auth::user();

        if (!$Authuser) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if ($Authuser->company_id !== $user->company_id) {
            return response()->json(['error' => 'No tienes permisos para editar este usuario'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'string|min:8|nullable',
            'role_id' => 'exists:roles,id|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->update($request->only(['name', 'email', 'role_id']));

        return response()->json($user);
    }

    // Eliminar un usuario
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Verifica si el usuario pertenece a la misma empresa
        if (Auth::user()->company_id !== $user->company_id) {
            return response()->json(['error' => 'No tienes permisos para eliminar este usuario'], 403);
        }

        // Verifica si el usuario es administrador
        if ($user->role->name === 'admin') {
            return response()->json(['error' => 'No puedes eliminar un usuario administrador'], 403);
        }

        // Verifica si el usuario intenta eliminarse a sí mismo
        if ($user->id === Auth::user()->id) {
            return response()->json(['error' => 'No puedes eliminar tu propio usuario'], 403);
        }

        // Elimina el usuario
        $user->delete();

        return response()->json(null, 204);
    }


    public function meProfile(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        return response()->json($user);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'string|min:8|nullable',
            'phone' => 'string|nullable',
            'address' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->update($request->only(['name', 'email']));

        return response()->json($user);
    }
}
