<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\Validator;
use App\Models\InvoiceTemplate;
use App\Imports\InvoiceImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CorrectedInvoicesExport;



class InvoiceController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search', ''); // Parámetro de búsqueda

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $invoices = Invoice::where('company_id', $user->company_id)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name_invoice', 'like', "%$search%")
                        ->orWhere('status', 'like', "%$search%");
                }
            })->paginate($perPage);

        return response()->json([
            'data' => $invoices,
        ]);
    }

    public function show($id)
    {

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $invoice = Invoice::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        return response()->json($invoice);
    }

    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file_id' => 'required|exists:files,id',
            'template_id' => 'required|string',
            'name_invoice' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $invoice = Invoice::create([
            'company_id' => $user->company_id,
            'user_id' => auth()->id(),
            'file_id' => $request->input('file_id'),
            'status' => 'pending',
            'name_invoice' => $request->input('name_invoice', 'Invoice'),
            'template_id' => $request->input('template_id'),
        ]);

        return response()->json(['message' => 'Factura creada', 'invoice' => $invoice]);
    }

    public function getTemplate($invoiceId)
    {

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $invoice = Invoice::where('id', $invoiceId)
            ->where('company_id', $user->company_id
            )->firstOrFail();

        if (!$invoice) {
            return response()->json(['errors' => 'La factura no existe'], 400);
        }

        $template_id = $invoice->template_id;

        $template = InvoiceTemplate::where('_id', $template_id)->firstOrFail();

        if (!$template) {
            return response()->json(['errors' => 'La plantilla no existe'], 400);
        }

        return response()->json(['template' => $template]);
    }

    public function getCorrectionRules($templateId)
    {
        $template = InvoiceTemplate::where('_id', $templateId)->with('correctionRules')->firstOrFail();
        return response()->json(['correction_rules' => $template->correctionRules]);
    }


    public function processInvoice($invoiceId)
    {
        // Recuperar la factura
        $invoice = Invoice::where('id', $invoiceId)
            ->where('company_id', auth()->user()->company_id)
            ->with('file') // Aseguramos cargar el archivo asociado
            ->firstOrFail();

        // Verificar la existencia de la plantilla asociada
        $template = InvoiceTemplate::where('_id', $invoice->template_id)
            ->with('correctionRules') // Cargar también las reglas de corrección
            ->firstOrFail();

        // Preparar las reglas de corrección
        $rules = $template->correctionRules->map(function ($rule) {
            return [
                'conditions' => $rule['conditions'],
                'corrections' => $rule['corrections'],
            ];
        })->toArray();

        // Verificar si el archivo físico existe
        $filePath = storage_path('app/private/' . $invoice->file->file_path);

        if (!file_exists($filePath)) {
            return response()->json(['errors' => 'El archivo no existe'], 400);
        }

        // Instanciar el importador con plantilla y reglas
        $importer = new InvoiceImport($template->toArray(), $rules);

        // Procesar el archivo Excel/CSV
        Excel::import($importer, $filePath);

        // Recuperar los datos procesados
        $processedData = $importer->processedData;

        return response()->json([
            'message' => 'Archivo procesado correctamente.',
            'data' => $processedData,
        ]);
    }

    public function processInvoiceDonwloade($invoiceId)
    {
         $invoice = Invoice::where('id', $invoiceId)
            ->where('company_id', auth()->user()->company_id)
            ->with('file')
            ->firstOrFail();

        $template = InvoiceTemplate::where('_id', $invoice->template_id)
            ->with('correctionRules')
            ->firstOrFail();

        $rules = $template->correctionRules->map(function ($rule) {
            return [
                'conditions' => $rule['conditions'],
                'corrections' => $rule['corrections'],
            ];
        })->toArray();

        $filePath = storage_path('app/private/' . $invoice->file->file_path);

        if (!file_exists($filePath)) {
            return response()->json(['errors' => 'El archivo no existe'], 400);
        }

        $importer = new InvoiceImport($template->toArray(), $rules);
        Excel::import($importer, $filePath);

        $processedData = $importer->processedData;

        // Columnas que requieren agregaciones
        $aggregations = $template->aggregations ?? [];

        // Generar el Excel corregido con fórmulas dinámicas
        return Excel::download(new CorrectedInvoicesExport($processedData, $aggregations), 'facturas_corregidas.xlsx');
    }

    public function update(Request $request, $id)
    {

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $invoice = Invoice::where('id', $id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name_invoice' => 'string',
            'status' => 'in:pending,corrected,rejected,paid',
            'date_to_pay' => 'date|nullable',
            'contact_id' => 'exists:contacts,id|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $invoice->update($request->all());

        return response()->json(['message' => 'Factura actualizada', 'invoice' => $invoice]);
    }


}
