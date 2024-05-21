<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    //
    public function upload(Request $request)
    {
        $request->validate([
            'file1' => 'required|file|max:5120', // Accepting all file types up to 5MB
            'file2' => 'required|file|max:5120', // Accepting all file types up to 5MB
        ]);

        $data = [];
        $file = $request->file('file1');
        $file_name = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('files'), $file_name);

        $uploadedFile = new File();
        $uploadedFile->filename = $file_name;
        $uploadedFile->path = 'files/' . $file_name;
        $uploadedFile->user_id = auth()->user()->id;
        $data[] = $uploadedFile->save();

        $file = $request->file('file2');
        $file_name = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('files'), $file_name);

        $uploadedFile = new File();
        $uploadedFile->filename = $file_name;
        $uploadedFile->path = 'files/' . $file_name;
        $uploadedFile->user_id = auth()->user()->id;
        $data[] = $uploadedFile->save();

        return response()->json([
            'message' => 'File uploaded successfully',
        ]);
    }

}
