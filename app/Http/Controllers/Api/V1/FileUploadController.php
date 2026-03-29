<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\Files\FileProcessorDispatcher;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function __construct(
        private FileProcessorDispatcher $fileProcessor
    ) {}

    public function upload(Request $request)
    {        
        $request->validate([
            'file' => 'required|file|mimes:xml,jpg,jpeg|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $dictionaryData = $this->fileProcessor->process($file);


// Сохранит в storage/app/example.txt
// Storage::put('example.txt', $dictionaryData['text']);
// Storage::append('example1.txt', $dictionaryData['text']);

// $content = file_get_contents(storage_path('app/example.txt'));

            return response()->json([
                'success' => true,
                'data' => $dictionaryData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
    
}
