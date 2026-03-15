<?php

namespace App\Services\Files\Processors;

use App\Services\Files\FileProcessorInterface;

use Illuminate\Http\UploadedFile;

abstract class BaseFileProcessor implements FileProcessorInterface
{
    protected array $supportedMimeTypes = [];
    
    public function supports(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), $this->supportedMimeTypes);
    }
    
    protected function validateStructure(array $data, array $expectedStructure): bool
    {
        // Базовая валидация структуры
        foreach ($expectedStructure as $key => $type) {
            if (!isset($data[$key]) || gettype($data[$key]) !== $type) {
                return false;
            }
        }
        return true;
    }
}