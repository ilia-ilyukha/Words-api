<?php

namespace App\Services;

use App\Http\Requests\Api\V1\StoreWordRequest;
use App\Http\Resources\V1\WordResource;
use App\Models\Word;
use App\Services\Translators\TranslationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;

class WordService
{
    public function prepareForStoreXML($xmlData)
    {
        $results = DB::transaction(function () use ($xmlData) {
            $words = [];

            if (isset($xmlData['words']['word']) && !is_array($xmlData['words']['word'])) {
                $xmlData['words']['word'] = [$xmlData['words']['word']]; // Wrap single word in an array
            }

            foreach ($xmlData['words']['word'] as $wordData) {
                $storeWordRequest = new StoreWordRequest([
                    'DE' => $wordData['de'] ?? null,
                    'RU' => $wordData['ru'] ?? null,
                    'words_capital_id' => (int)$xmlData['@words_capital'] ?? 0,
                ]);

                $words[] = $this->store($storeWordRequest);
            }

            return $words;
        });
        return $results;
    }
    /**
     * Create a new word resource
     */
    public function store(StoreWordRequest $request): WordResource
    {
        return new WordResource(Word::create([
            'DE' => $request->input('DE'),
            'RU' => $request->input('RU'),
            'words_capital_id' => $request->input('words_capital_id') ?? 0,
        ]));
        // return new WordResource(Word::create($request->mappedAttributes()));
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
        Log::info('Generating sentence for word', [
            'word' => $text,
            'target_language' => $targetLang
        ]);
        $promt = 'Create a simple German sentence using the word B2 levels "' . $text . '". 
            Return only the sentences and translations in Russian, in string format, without any explanations. 
            The string should have the following structure: 
            Generated sentence in German. Divider | Translation of the sentence in Russian. 
            For example: "Generated sentence in German | Translation of the sentence in Russian"';

        // $promt = 'Create a simple German sentence using the word (A2, B1, B2 levels) "' . $text . '". 
        //     Return only the sentences and translations in Russian, in JSON format, without any explanations. 
        //     The JSON should have the following structure: 
        //     {
        //         "word": "The input word",
        //         "translation": "Translation of the input word in Russian",
        //         "sentences": {
        //             "A1": {
        //                 "DE": "Generated sentence in German"
        //                 "RU": "Translation of the sentence in Russian"
        //             },
        //             "A2": {
        //                 "DE":  "Generated sentence in German"
        //                 "RU":  "Translation of the sentence in Russian"
        //             },
        //             "B1": {
        //                 "DE": "Generated sentence in German"
        //                 "RU": "Translation of the sentence in Russian"
        //             },
        //             "B2": {
        //                 "DE": "Generated sentence in German"
        //                 "RU": "Translation of the sentence in Russian"
        //             }
        //         }
        //     }';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => env('OPENROUTER_API_MODEL'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $promt
                ]
            ]
        ]);

        if (!$response->json()['choices'][0]['message']['content']) {
            Log::info('Received response from OpenRouter', [
                'response' => $response->json()
            ]);
        }
        
        $results = $response->json()['choices'][0]['message']['content'] ?? 'No response';
        $results = explode("|", $results);
        // TODO: Create a resource for this response and return it, instead of array with strings
        return [
            'type' => 'sentences',
            'word'  => $text,
            'sentences' => [
                "B2" => [
                    'DE' => $results[0] ?? '',
                    'RU' => $results[1] ?? '',
                ],
            ],
        ];
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
    public function deleteWord(int $id): bool
    {
        try {
            $record = Word::findOrFail($id);

            // Логируем удаление
            Log::info('Deleting dictionary record', [
                'id' => $id,
                'data' => $record->toArray(),
                // 'user' => auth()->id()
            ]);

            return (bool) $record->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete dictionary record', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function deleteMultipleRecords(array $ids): array
    {
        DB::beginTransaction();

        try {
            $deletedCount = Word::whereIn('id', $ids)->delete();
            $failedIds = array_diff($ids, $this->getExistingIds($ids));

            DB::commit();

            Log::info('Batch deletion completed', [
                'total_requested' => count($ids),
                'deleted' => $deletedCount,
                'failed' => $failedIds
            ]);

            return [
                'deleted' => $deletedCount,
                'failed' => $failedIds
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch deletion failed', [
                'ids' => $ids,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getExistingIds(array $ids): array
    {
        return Word::whereIn('id', $ids)->pluck('id')->toArray();
    }

    public function generateSentences($words = [])
    {
        $sentences = [];
        
        foreach ($words as $word) {
            $sentence = $this->generateSentence($word->DE, 'de');
            // $sentences = array_merge($sentences, $this->wordService->generateSentence($word->DE, 'de'));
            $sentenceService = new SentenceService();
            $sentenceService->storeSentence(
                $sentence['sentences']['B2']['DE'] ?? null,
                $sentence['sentences']['B2']['RU'] ?? null,
                $word->id
            );

            $sentences[] = [
                'word' => $word->DE,
                'sentence' => $sentence['sentences']['B2']['DE'] ?? null,
            ];
        }
        return $sentences;
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
