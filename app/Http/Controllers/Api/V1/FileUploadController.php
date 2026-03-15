<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWordRequest;
use App\Services\WordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Storage;

use App\Services\Files\FileProcessorDispatcher;

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

            // Сохраняем данные в БД или обрабатываем дальше
            // Dictionary::create($dictionaryData);

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
    // public function upload(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'file' => 'required|mimes:xml,csv|max:4096',
    //         ]);

    //         $file = $request->file('file');
    //         $path = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');

    //         // Read and parse XML data
    //         $xmlData = $this->getDataFromXml($path);

    //         // Save data to the database
    //         $wordService = new WordService();
    //         $dbResults = $wordService->prepareForStoreXML($xmlData);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'File uploaded successfully!',
    //             'file_path' => $path,
    //             'data' => $dbResults
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'File upload failed',
    //             'error' => $e->getMessage()
    //         ], 400);
    //     }
    // }

   
}
