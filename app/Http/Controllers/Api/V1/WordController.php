<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\WordFilter;
use App\Http\Resources\V1\WordResource;
use App\Models\Word;
use App\Services\Translators\TranslationService;
use App\Services\WordService;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf; // Импорт фасада
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WordController extends Controller
{
    private WordService $wordService;
    private TranslationService $translationService;

    public function __construct(WordService $wordservice, TranslationService $translationService)
    {
        $this->wordService = $wordservice;
        $this->translationService = $translationService;
    }
     
    /**
     * Display a listing of the resource.
     */
    public function index(WordFilter $filters)
    {
        $words = Word::filter($filters)->get();
        return WordResource::collection($words);
    }

    //TODO:
    /**
     * Get all capitals.
     */
    public function getCapitals()
    {
        $capitals = Word::distinct()->pluck('words_capital_id');
        return $capitals;
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $word = Word::with('sentences')->findOrFail($id);

        return new WordResource($word);
    }

    public function downloadPdf(WordFilter $filters, Request $request)
    {
        $words = Word::filter($filters)->with(['sentences' => function ($q) {
            $q->limit(1);
        }])->get();
        
        $pdf = Pdf::loadView('pdf.pdf_words', [
            'items' => WordResource::collection($words),
            'capital' => $request->filter['capital'] // TODO: Add validation ???
        ]);

        return $pdf->download('words.pdf');
    }


    public function readText()
    {
        $words = file_get_contents(storage_path('app/example.txt'));
        $words = explode("\n", $words);

        // TODO:Create abstract class for translation and use it here and in translate method
        // $resuls = $this->translationService->translate($words, 'ru');
        $resuls = $this->translationService->translateAI($words, 'ru');

        // dd($words, $resuls);
        // $resuls = $this->wordService->libreTranslate($words, 'auto', 'ru');

        return $resuls;
    }

    public function translate(Request $request)
    {
        $request->validate([
            'words' => 'required|array',
            // 'target_lang' => 'required|string'
        ]);
        $resuls = $this->translationService->translateAI($request->input('words'), $request->input('target_lang'));

        return $resuls;
    }

    public function generateSentence(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $sentence = $this->wordService->generateSentence($request->input('text'), 'deu');
        
        // TODO: Create a resource for this response and return it, instead of array with strings
        return $sentence;
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        try {
            $result = $this->wordService->deleteMultipleRecords($request->ids);

            return response()->json([
                'success' => true,
                'message' => "Deleted {$result['deleted']} records",
                'deleted_count' => $result['deleted'],
                'failed_ids' => $result['failed']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
