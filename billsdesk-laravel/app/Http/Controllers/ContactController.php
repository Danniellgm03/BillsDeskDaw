<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Contact;
use App\Models\Company;

class ContactController extends Controller
{
    /**
     * Listar todos los contactos de la empresa del usuario autenticado.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Empresa no encontrada para el usuario autenticado'], 404);
        }

        $contacts = $company->contacts; // Relación definida en el modelo Company
        return response()->json($contacts);
    }

    /**
     * Crear un nuevo contacto para la empresa del usuario autenticado.
     */
    public function store(Request $request)
    {

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Empresa no encontrada para el usuario autenticado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact = $company->contacts()->create($request->all());
        return response()->json($contact, 201);
    }

    /**
     * Mostrar un contacto específico de la empresa del usuario autenticado.
     */
    public function show($contactId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Empresa no encontrada para el usuario autenticado'], 404);
        }

        $contact = $company->contacts()->find($contactId);

        if (!$contact) {
            return response()->json(['error' => 'Contacto no encontrado'], 404);
        }

        return response()->json($contact);
    }

    /**
     * Actualizar un contacto de la empresa del usuario autenticado.
     */
    public function update(Request $request, $contactId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }


        $company = $user->company;

        if (!$company) {
            return response()->json(['error' => 'Empresa no encontrada para el usuario autenticado'], 404);
        }

        $contact = $company->contacts()->find($contactId);

        if (!$contact) {
            return response()->json(['error' => 'Contacto no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contact->update($request->all());
        return response()->json($contact);
    }

    /**
     * Eliminar un contacto de la empresa del usuario autenticado.
     */
    public function destroy($contactId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $company = $user->company;



        if (!$company) {
            return response()->json(['error' => 'Empresa no encontrada para el usuario autenticado'], 404);
        }

        $contact = $company->contacts()->find($contactId);

        if (!$contact) {
            return response()->json(['error' => 'Contacto no encontrado'], 404);
        }

        $contact->delete();
        return response()->json(null, 204);
    }
}
