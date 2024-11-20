<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Validator;


class FileController extends Controller
{

    public function index(Request $request)
    {
        $files = File::where('company_id', auth()->
            user()->company_id)->get();

        return response()->json([
            'data' => $files,
        ]);
    }

    // if not is the same company or user, return 403
    public function show(Request $request, $id)
    {
        $file = File::where('company_id', auth()->
            user()->company_id)->find($id);


        $user_logeed = auth()->id();
        $company_logeed = auth()->user()->company_id;

        if ($file->created_by != $user_logeed && $file->company_id != $company_logeed) {
            return response()->json([
                'message' => 'You can not see this file',
            ], 403);
        }

        if (!$file) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
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
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,html,htm,xml,csv,zip,rar|max:2048',
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
        $file = File::where('company_id', auth()->
            user()->company_id)->find($id);

        $user_logeed = auth()->id();
        $company_logeed = auth()->user()->company_id;

        if ($file->created_by != $user_logeed && $file->company_id != $company_logeed) {
            return response()->json([
                'message' => 'You can not download this file',
            ], 403);
        }

        if (!$file) {
            return response()->json([
                'message' => 'File not found',
            ], 404);
        }

        return response()->download(storage_path('app/' . $file->file_path));
    }

}
