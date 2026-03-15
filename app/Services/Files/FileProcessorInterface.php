<?php

namespace App\Services\Files;

use Illuminate\Http\UploadedFile;

interface FileProcessorInterface
{
    /**
     * Проверяет, может ли обработчик работать с данным файлом
     */
    public function supports(UploadedFile $file): bool;
    
    /**
     * Обрабатывает файл и возвращает извлеченные данные
     */
    public function process(UploadedFile $file): array;
    
    /**
     * Возвращает ожидаемую структуру данных для данного типа файла
     */
    // public function getExpectedStructure(): array;
}