<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;


class CompanyController extends Controller
{

    public function index(){
        $companies = Company::all();
        return response()->json($companies);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $company = Company::create($request->all());
        return response()->json($company, 201);
    }

    public function show($id){
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        return response()->json($company);
    }

    public function update(Request $request, $id){
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $company->update($request->all());
        return response()->json($company);
    }

    public function destroy($id){
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['error' => 'Empresa no encontrada'], 404);
        }

        $company->delete();
        return response()->json(['message' => 'Empresa eliminada']);
    }

    public function meCompany(){
        return response()->json(Auth::user());
    }

}
