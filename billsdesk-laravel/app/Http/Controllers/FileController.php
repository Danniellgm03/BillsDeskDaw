<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;



class FileController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $search = $request->input('search', ''); // Parámetro de búsqueda
        $isFav = $request->input('is_fav'); // Parámetro para filtrar por 'is_fav'
        $limit = $request->input('limit', 5); // Limite de resultados (si no se pasa, no se aplica)

        $files = File::where('company_id', auth()->user()->company_id)
                    ->where(function ($query) use ($search) {
                        if ($search) {
                            $query->where('file_name', 'like', "%$search%")
                                ->orWhere('description', 'like', "%$search%");
                        }
                    })
                    ->when($isFav !== null, function ($query) use ($isFav) {
                        // Si is_fav está presente en la solicitud, filtra por ese valor
                        $query->where('is_fav', (bool) $isFav);
                    })->paginate($perPage);

        return response()->json([
            'data' => $files,
        ]);
    }


    // if not is the same company or user, return 403
    public function show(Request $request, $id)
    {
        $file = File::where('company_id', auth()->user()->company_id)->find($id);

        if (!$file) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        $user_logged = auth()->id();
        $company_logged = auth()->user()->company_id;

        if ($file->created_by != $user_logged && $file->company_id != $company_logged) {
            return response()->json([
                'message' => 'You can not see this file',
            ], 403);
        }

        return response()->json([
            'data' => $file,
        ]);
    }


    public function showByCompany(Request $request, $company_id)
    {
        $files = File::where('company_id', $company_id)->get();

        $company_logeed = auth()->user()->company_id;

        if ($company_logeed != $company_id) {
            return response()->json([
                'message' => 'You can not see the files of this company',
            ], 403);
        }

        return response()->json([
            'data' => $files,
        ]);
    }

    public function showByUser(Request $request, $user_id)
    {
        $files = File::where('created_by', $user_id)->get();

        $user_logeed = auth()->id();

        if ($user_logeed != $user_id) {
            return response()->json([
                'message' => 'You can not see the files of this user',
            ], 403);
        }

        return response()->json([
            'data' => $files,
        ]);
    }



    public function uploadFile(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xls,xlsx,csv|max:2048',
            'file_type' => 'required|in:invoice,other',
            'file_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $file = $request->file('file');
        $company_id = auth()->user()->company_id;
        $created_by = auth()->id();
        $file_name = $file->getClientOriginalName();
        $file_extension = $file->getClientOriginalExtension();
        $file_size = $file->getSize();
        $file_mime_type = $file->getMimeType();
        $file_path = $file->store('files');
        $file_status = 'active';

        $file = File::create([
            'company_id' => $company_id,
            'file_type' => $request->file_type,
            'file_path' => $file_path,
            'file_name' => $file_name,
            'file_extension' => $file_extension,
            'file_size' => $file_size,
            'file_size_type' => 'bytes',
            'file_mime_type' => $file_mime_type,
            'file_description' => $request->file_description,
            'file_status' => $file_status,
            'created_by' => $created_by,
            'updated_by' => $created_by,
            'is_fav' => false,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => $file,
        ]);
    }


    //if not is the same company whch loged and user who created the file, return 403
    public function deleteFile(Request $request, $id)
    {
        $file = File::where('company_id', auth()->
            user()->company_id)->find($id);

        $user_logeed = auth()->id();
        $company_logeed = auth()->user()->company_id;

        if ($file->created_by != $user_logeed && $file->company_id != $company_logeed) {
            return response()->json([
                'message' => 'You can not delete this file',
            ], 403);
        }

        if (!$file) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        $file->delete();

        return response()->json([
            'message' => 'File deleted successfully',
        ]);
    }

    public function downloadFile(Request $request, $id)
    {
        $file = File::where('company_id', auth()->user()->company_id)->find($id);

        if (!$file) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        $user_logged = auth()->id();
        $company_logged = auth()->user()->company_id;

        if ($file->created_by != $user_logged && $file->company_id != $company_logged) {
            return response()->json([
                'message' => 'You cannot download this file',
            ], 403);
        }

        //get file path
        $filePath = storage_path('app/private/' . $file->file_path);

        //check if file exists
        if (!file_exists($filePath)) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }



        return response()->download($filePath);
    }


    public function search(Request $request)
    {
        $search = $request->search;

        $files = File::where('company_id', auth()->
            user()->company_id)->where('file_name', 'like', '%' . $search . '%')->get();

        return response()->json([
            'data' => $files,
        ]);
    }

    public function updateFile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'file_description' => 'nullable|string',
            'is_fav' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $file = File::where('company_id', auth()->
            user()->company_id)->find($id);

        if (!$file) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        $file->file_description = $request->file_description ?? $file->file_description;
        $file->is_fav = $request->is_fav;
        $file->save();

        return response()->json([
            'message' => 'File updated successfully',
            'data' => $file,
        ]);
    }

    public function getCsvHeaders(Request $request, $id)
    {
        // Buscar el archivo en la base de datos
        $file = File::where('company_id', auth()->user()->company_id)->find($id);

        if (!$file) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        // Obtener la ruta completa del archivo CSV
        $filePath = storage_path('app/private/' . $file->file_path);

        // Verificar si el archivo existe
        if (!file_exists($filePath)) {
            return response()->json([
                'message' => 'File not found in storage',
            ], 404);
        }

        // Leer la primera fila (cabecera) del archivo CSV usando Maatwebsite/Excel
        try {
            $headers = Excel::toArray([], $filePath)[0][0]; // Obtener la primera fila de la primera hoja

            return response()->json([
                'data' => $headers, // Devolver las cabeceras
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error reading CSV file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
