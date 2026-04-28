<?php

namespace App\Services\Translators;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected string $apiUrl;

    public function __construct()
    {
        // Адрес, где висит наш Node.js микросервис
        $this->apiUrl = env('TRANSLATE_SERVICE_URL', 'http://127.0.0.1:3000/translate');
    }

    
    public function translate($wordsToTranslate = [], $targetLang = 'ru')
    {
        if (empty($wordsToTranslate)) {
            return response()->json(['success' => false, 'message' => 'No words provided for translation'], 400);
        }

        $translatedWords = $this->translateBatch($wordsToTranslate, $targetLang);

        if ($translatedWords) {
            // Объединяем исходные и переведенные слова для наглядности
            $result = array_combine($wordsToTranslate, $translatedWords);
            return response()->json(['success' => true, 'data' => $result]);
        } else {
            return response()->json(['success' => false, 'message' => 'Не удалось выполнить перевод'], 500);
        }
    }

    /**
     * Перевести массив слов с помощью внешнего микросервиса
     *
     * @param array $words Массив слов для перевода
     * @param string $to На какой язык переводим (код, например, 'ru', 'en', 'es')
     * @param string|null $from С какого языка (null = автоопределение)
     * @return array|null Массив переведенных слов или null при ошибке
     */
    private function translateBatch(array $words, string $to, ?string $from = null): ?array
    {
        if (empty($words)) {
            return [];
        }

        try {
            $response = Http::timeout(30)->post($this->apiUrl, [
                'words' => $words,
                'from' => $from,
                'to' => $to,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Предполагаем, что микросервис вернул { "translated": [...] }
                return $data['translated'] ?? null;
            }

            Log::error('Ошибка ответа от сервиса перевода', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Исключение при вызове сервиса перевода: ' . $e->getMessage());
            return null;
        }
    }

    
    public function translateAI($words = [], $targetLang = 'ru')
    {
        $promt = '';
        if (is_array($words)) {
            $promt = 'Translate the following words into ' . $targetLang . ': ' . implode(', ', $words) . '. 
                Return ONLY valid JSON array, without any explanations or markdown.
                The JSON must be a valid array with this exact structure: { 
                    "original": "The input word", 
                    "translation": "Translation of the input word in ' . $targetLang . '" 
                }';
        } else {
            $promt = 'Translate the following word into ' . $targetLang . ': ' . $words . '. Return only the translation in JSON format, without any explanations. The JSON should have the following structure: { "original": "The input word", "translation": "Translation of the input word in ' . $targetLang . '" }';
        }
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => env('OPENROUTER_API_MODEL', 'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning:free'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $promt
                ]
            ]
        ]);

        // Normalize the response to extract the JSON content
        $ai_response = $response->json()['choices'][0]['message']['content'] ?? '';
        $cleaned = preg_replace('/^```json\s*|\s*```$/m', '', trim($ai_response));
        $data = json_decode($cleaned, true);

        return $data ?? ['error' => 'Invalid response'];
    }

}
