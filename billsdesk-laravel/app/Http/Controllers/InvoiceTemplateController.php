<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvoiceTemplate;
use Illuminate\Support\Facades\Validator;


class InvoiceTemplateController extends Controller
{
    public function index()
    {
        $templates = InvoiceTemplate::where('company_id', auth()->user()->company_id)->get();
        return response()->json($templates);
    }

    public function show($id)
    {
        $template = InvoiceTemplate::where('_id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        return response()->json($template);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'column_mappings' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $template = InvoiceTemplate::create([
            'company_id' => auth()->user()->company_id,
            'template_name' => $request->input('template_name'),
            'column_mappings' => $request->input('column_mappings'),
            'formulas' => $request->input('formulas', []),
            'validation_rules' => $request->input('validation_rules', []),
            'aggregations' => $request->input('aggregations', []),
        ]);

        return response()->json(['message' => 'Plantilla creada', 'template' => $template]);
    }

    //update but if attribute has not changed, do not update
    public function update(Request $request, $id)
    {
        $template = InvoiceTemplate::where('_id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'template_name' => 'string|max:255',
            'column_mappings' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $template->fill($request->all());
        $template->save();

        return response()->json(['message' => 'Plantilla actualizada', 'template' => $template]);
    }

}
