<?php

namespace App\Services;

use App\Http\Requests\Api\V1\StoreWordRequest;
use App\Http\Resources\V1\WordResource;
use App\Models\Word;
use App\Services\Translators\TranslationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use thiagoalessio\TesseractOCR\TesseractOCR;

class WordService
{
    public function prepareForStoreXML($xmlData)
    {
        $results = DB::transaction(function () use ($xmlData) {
            $words = [];
            $wordService = new WordService();

            foreach ($xmlData['word'] as $wordData) {
                $storeWordRequest = new StoreWordRequest([
                    'DE' => $wordData['de'] ?? null,
                    'RU' => $wordData['ru'] ?? null,
                    'words_capital_id' => (int)$xmlData['@words_capital'] ?? 0,
                ]);

                $words[] = $wordService->store($storeWordRequest);
            }

            return $words;
        });
        return $results;
    }
    /**
     * Create a new word resource
     */
    public function store(StoreWordRequest $request)
    {
        //TODO: Do not works, for some reason
        // $request->validated($request->all());
        try {
            // //policy
            // $this->isAble('store', Word::class);

            return new WordResource(Word::create([
                'DE' => $request->input('DE'),
                'RU' => $request->input('RU'),
                'words_capital_id' => $request->input('words_capital_id'),
            ]));
            // return new WordResource(Word::create($request->mappedAttributes()));
        } catch (AuthorizationException $ex) {
            return response()->json([
                'message' => 'You are not authorize to update this resource',
                'status' => 401
            ], 401);
        }
    }

    public function readText($file_path)
    {
        $TESSERACT_PATH = "C:/Program Files/Tesseract-OCR/tesseract.exe";
        $TESSDATA = "C:/Program Files/Tesseract-OCR/tessdata";

        $file = storage_path($file_path);
        $text = (new TesseractOCR($file))
            ->executable($TESSERACT_PATH)
            ->tessdataDir($TESSDATA)
            ->psm(6)
            ->lang('deu')
            ->run();
        // $text = (new TesseractOCR($file))
        //     ->executable(env('TESSERACT_PATH'))
        //     ->tessdataDir(env('TESSDATA'))
        //     ->psm(6)
        //     ->lang('deu')
        //     ->run();

        $words_array = preg_split('/\R+/', trim($text));
        $words_short_str = implode('| ', array_slice($words_array, 0, 10));

        return [
            'text' => $text,
            'words' => $words_array,
            'words_short_string' => $words_short_str
        ];
        // return response()->json();
    }

    public function libreTranslate($words = [], $source = 'auto', $targetLang = 'ru')
    {
        // $words = ["Haus", "Baum", "Auto"];
        $translations = [];
        $uri = env('LIBRETRANSLATE_URI', 'http://127.0.0.1:5000');
        try {
            $response = Http::post($uri . '/translate', [
                'q' => $words,
                'source' => $source,
                'target' => $targetLang,
                'format' => 'text',
                'alternatives' => 3,
                'api_key' => null     // LibreTranslate on Localhost does not require an API key, but you can set one if needed
            ]);

            $translations = $response->json()['translatedText'] ?? [];
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $translations;
    }


    public function generateSentence($text = "", $targetLang = 'de')
    {
        // $promt = "Generate a sentence in $targetLang language using the following words: $text";
        $promt = 'Create a simple German sentence using the word (A2, B1, B2 levels) "' . $text . '". Return only the sentences and translations in Russian';
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'google/gemma-2-9b-it',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $promt
                ]
            ]
        ]);

        return $response->json()['choices'][0]['message']['content'] ?? 'No response';
    }
    /**
     * Read a word resource by ID
     */
    public function read(string $id): ?array
    {
        // Fetch from database or cache
        return null;
    }

    /**
     * Update a word resource
     */
    public function update(string $id, array $data): bool
    {
        // Update logic here
        return true;
    }

    /**
     * Delete a word resource
     */
    public function delete(string $id): bool
    {
        // Delete logic here
        return true;
    }

    /**
     * Get all words
     */
    public function getAll(): array
    {
        // Fetch all words from database
        return [];
    }

    /**
     * Search words by keyword
     */
    public function search(string $keyword): array
    {
        // Search logic here
        return [];
    }
}
