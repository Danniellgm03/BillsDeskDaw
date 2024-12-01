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
    public function index()
    {
        $invoices = Invoice::where('company_id', auth()->user()->company_id)->get();
        return response()->json($invoices);
    }

    public function show($id)
    {
        $invoice = Invoice::where('id', $id)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        return response()->json($invoice);
    }

    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file_id' => 'required|exists:files,id',
            'template_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $invoice = Invoice::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'file_id' => $request->input('file_id'),
            'status' => 'pending',
            'template_id' => $request->input('template_id'),
        ]);

        return response()->json(['message' => 'Factura creada', 'invoice' => $invoice]);
    }

    public function getTemplate($invoiceId)
    {
        $invoice = Invoice::where('id', $invoiceId)
            ->where('company_id', auth()->user()->company_id
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
}