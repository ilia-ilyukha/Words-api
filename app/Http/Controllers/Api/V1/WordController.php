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

    public function downloadPdf(WordFilter $filters, Request $request)
    {
        $words = Word::filter($filters)->get();

        $pdf = Pdf::loadView('pdf.pdf_words', [
            'items' => WordResource::collection($words),
            'capital' => $request->filter['capital'] // TODO: Add validation ???
        ]);

        return $pdf->download('words.pdf');
    }


    public function readText()
    {
        $file_path = 'app/public/images/words.jpg';

        // $words = $this->wordService->readText($file_path)['words_short_string'] ?? '';
        $words = $this->wordService->readText($file_path)['words'] ?? [];

        if (empty($words)) {
            return response()->json([
                'message' => 'No words found in the image',
                'status' => 404
            ], 404);
        }

        // TODO:Create abstract class for translation and use it here and in translate method
        $resuls = $this->translationService->translate($words, 'ru');
        
        // dd($words, $resuls);
        // $resuls = $this->wordService->libreTranslate($words, 'auto', 'ru');

        return $resuls;
    }

    public function generateSentence(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        return $this->wordService->generateSentence($request->input('text'), 'deu');
    }
}
