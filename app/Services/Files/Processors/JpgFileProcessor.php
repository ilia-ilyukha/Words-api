<?php

namespace App\Services\Files\Processors;

use App\Services\Files\Processors\BaseFileProcessor;
use Illuminate\Http\UploadedFile;
use thiagoalessio\TesseractOCR\TesseractOCR;

class JpgFileProcessor extends BaseFileProcessor
{
    protected array $supportedMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/pjpeg'
    ];

    public function process(UploadedFile $file): array
    {
        $TESSERACT_PATH = "C:/Program Files/Tesseract-OCR/tesseract.exe";
        $TESSDATA = "C:/Program Files/Tesseract-OCR/tessdata";

        $text = (new TesseractOCR($file->getRealPath()))
            ->executable($TESSERACT_PATH)
            ->tessdataDir($TESSDATA)
            ->psm(6)
            ->lang('deu')
            ->run();


        $words_array = preg_split('/\R+/', trim($text));
        $words_short_str = implode('| ', array_slice($words_array, 0, 10));

        return [
            'text' => $text,
            'words' => $words_array,
            'words_short_string' => $words_short_str
        ];
        // return response()->json();
    }

    public function getExpectedStructure(): array
    {
        return [
            'words_capital' => 'integer',
            'title' => 'string',
            'words' => 'array'
        ];
    }

    private function parseTextToDictionary(string $text): array
    {
        // Сложная логика парсинга текста из изображения
        // Нужно извлечь заголовок, количество слов и сами слова

        $lines = explode("\n", $text);
        $result = [
            'words_capital' => 0,
            'title' => '',
            'words' => []
        ];

        // Пример простого парсинга (в реальности будет сложнее)
        foreach ($lines as $line) {
            if (preg_match('/^#\s+(.+)$/', $line, $matches)) {
                $result['title'] = trim($matches[1]);
            } elseif (preg_match('/^-\s+([^,]+),\s*(.+)$/', $line, $matches)) {
                $result['words'][] = [
                    'de' => trim($matches[1]),
                    'ru' => trim($matches[2])
                ];
            }
        }

        $result['words_capital'] = count($result['words']);

        return $result;
    }
}
