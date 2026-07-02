<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\WordFilter;
use App\Http\Requests\Api\V1\StoreWordRequest;
use App\Http\Resources\V1\WordResource;
use App\Models\Word;
use App\Services\SentenceService;
use App\Services\Translators\TranslationService;
use App\Services\WordService;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf; // Импорт фасада
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Contracts\Service\Test\ServiceLocatorTest;

class WordController extends ApiController
{
    private WordService $wordService;
    private TranslationService $translationService;
    private SentenceService $sentenceService;

    public function __construct(WordService $wordservice, TranslationService $translationService, SentenceService $sentenceService)
    {
        $this->wordService = $wordservice;
        $this->translationService = $translationService;
        $this->sentenceService = $sentenceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(WordFilter $filters)
    {
        $words = Word::filter($filters)->get();
        return WordResource::collection($words);
    }

    /**
     * Get all capitals.
     */
    public function getCapitals()
    {
        $capitals = Word::distinct()->orderBy('words_capital_id', 'desc')->pluck('words_capital_id');
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

    /**
     * Create a new word resource
     */
    public function store(StoreWordRequest $request)
    {
        $data = $request->validated();
        try {
            $results = [];

            foreach ($data['words'] as $wordData) {
                $results[] = $this->wordService->store(new StoreWordRequest($wordData));
            }

            return $this->ok(
                'Words created successfully',
                [
                    'words' => $results,
                    'capital_id' => $request->capital_id,
                    'created_count' => count($results)
                ]
            );
        } catch (AuthorizationException $ex) {
            return $this->ok('You are not authorized to create this resource' . $ex->getMessage(), 401);
        }
    }

    public function downloadPdf(WordFilter $filters, Request $request)
    {
        $request->validate([
            'filter.capital' => 'sometimes|integer'
        ]);
        $selectedColums = [
            'DE',
            'RU',
            // 'sentences'
        ];
        $words = Word::filter($filters)->with(['sentences' => function ($q) {
            $q->limit(1);
        }])
        ->orderBy('id')
        ->get();

        $pdf = Pdf::loadView('pdf.pdf_words', [
            'items' => WordResource::collection($words),
            'capital' => $request->filter['capital'], // TODO: Add validation ???
            'selectedColums' => $selectedColums,
            'fontSize' => 13 + (5 - count($selectedColums)) // Adjust font size based on the number of columns
        ]);

        return $pdf->download('words_' . $request->filter['capital'] . '.pdf');
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

        try {
            // $results = $this->translationService->translateAI($request->input('words'), $request->input('target_lang'));
            $results =  [
                [

                    "original" => "vertraut sein (mit + D.)",
                    "translation" => "быть знакомым с кем-либо/чем-либо"
                ],
                [
                    "original" => "sich vertraut machen (mit + D.)",
                    "translation" => "ознакомиться с кем-либо/чем-либо"
                ],
                [
                    "original" => "vertraulich",
                    "translation" => "конфиденциальный, доверительный"
                ]
            ];
            // dd($results);
            return $this->ok(
                'Words translated successfully',
                [
                    'words' => $results,
                ]
            );
        } catch (\Exception $e) {
            return $this->ok('Something went wrong: ' . $e->getMessage(), 200);
        }
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

    //TODO: Devide big query into smaller ones, and create a resource for this response and return it, instead of array with strings
    public function generateSentencesForCapital(Request $request)
    {
        $request->validate([
            'capital_id' => 'required|integer',
            'limit' => 'sometimes|integer|max:20' // Optional limit parameter with a maximum of 20
        ]);

        try {
            $words = Word::doesntHave('sentences')->where('words_capital_id', $request->capital_id)->limit($request->input('limit', 10))->get();

            $sentences = $this->wordService->generateSentences($words);

            return $this->ok(
                'Sentences generated successfully',
                [
                    'sentences' => $sentences,
                    'capital_id' => $request->capital_id,
                    'generated_count' => count($sentences)
                ]
            );
        } catch (\Exception $e) {
            return $this->ok('Something went wrong: ' . $e->getMessage(), 200);
        }
    }
}
