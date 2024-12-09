<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRule;
use Illuminate\Support\Facades\Validator;
use App\Models\InvoiceTemplate;

class CorrectionRuleController extends Controller
{
    public function index()
    {

        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $rules = CorrectionRule::where('company_id', $user->company_id)->get();
        return response()->json($rules);
    }

    public function getByTemplateId($templateId)
    {

        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $template = InvoiceTemplate::where('_id', $templateId)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $rules = $template->correctionRules;
        return response()->json($rules);
    }

    public function show($id)
    {
        $rule = CorrectionRule::where('_id', $id)->firstOrFail();
        return response()->json($rule);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'rule_name' => 'required|string|max:255',
            'conditions' => 'required|array|size:1',
            'conditions.*' => 'required|array',
            'corrections' => 'required|array|size:1',
            'corrections.*' => 'required|array',
            'template_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $template = InvoiceTemplate::where('_id', $request->input('template_id'))
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $rule = CorrectionRule::create([
            'company_id' => $user->company_id,
            'rule_name' => $request->input('rule_name'),
            'conditions' => $request->input('conditions'),
            'corrections' => $request->input('corrections'),
            'template_id' => $request->input('template_id'),
        ]);

        return response()->json(['message' => 'Regla de corrección creada', 'rule' => $rule]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rule_name' => 'nullable|string|max:255',
            'conditions' => 'required|nullable|array|size:1',
            'conditions.*' => 'required|array',
            'corrections' => 'required|nullable|array|size:1',
            'corrections.*' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $rule = CorrectionRule::where('_id', $id)->firstOrFail();
        $rule->update($request->only('rule_name', 'conditions', 'corrections'));

        return response()->json(['message' => 'Regla de corrección actualizada', 'rule' => $rule]);
    }

    public function destroy($id)
    {
        $rule = CorrectionRule::where('_id', $id)->firstOrFail();
        $rule->delete();

        return response()->json(['message' => 'Regla de corrección eliminada']);
    }
}
