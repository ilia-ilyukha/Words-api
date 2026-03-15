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
        if(empty($wordsToTranslate)) {
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
}