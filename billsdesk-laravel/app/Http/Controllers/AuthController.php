<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\Invitation;
use Illuminate\Support\Facades\Password;
use App\Notifications\ForgotPasswordNotification;



class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('token-name')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'permissions' => $user->role->permissions
        ]);
    }

    public function register(Request $request)
    {
          try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422); // Código de estado 422 Unprocessable Entity
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 1,
        ]);

        $token = $user->createToken('token-name')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'permissions' => $user->role->permissions
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }

    public function registerWithInvitation(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $invitation = Invitation::where('email', $request->email)
            ->where('token', $request->token)
            ->where('accepted', false)
            ->first();

        if (!$invitation) {
            return response()->json(['error' => 'Invitación no válida'], 404);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $invitation->role_id,
            'company_id' => $invitation->company_id,
        ]);

        $invitation->accepted = true;
        $invitation->save();

        $token = $user->createToken('token-name')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'permissions' => $user->role->permissions
        ], 201);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Si el correo existe, se enviará un email con instrucciones'], 200);
        }

        // Generar el token
        $token = Password::broker()->createToken($user);

        // Enviar la notificación personalizada
        $user->notify(new ForgotPasswordNotification($token));

        return response()->json(['message' => 'Si el correo existe, se enviará un email con instrucciones'], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Buscar el usuario por email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if(!Password::broker()->tokenExists($user, $request->token)) {
            return response()->json(['error' => 'Token no válido'], 404);
        }

        // Resetear la contraseña
        Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return response()->json(['message' => 'Contraseña restablecida correctamente'], 200);
    }

    function isValidTokenResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if(!Password::broker()->tokenExists($user, $request->token)) {
            return response()->json(['error' => 'Token no válido'], 404);
        }

        return response()->json(['message' => 'Token válido'], 200);
    }

}
