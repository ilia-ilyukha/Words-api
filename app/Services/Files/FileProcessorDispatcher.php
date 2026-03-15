<?php

namespace App\Services\Files;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

use App\Services\Files\FileProcessorInterface;

class FileProcessorDispatcher
{
    private array $processors = [];
    
    public function addProcessor(FileProcessorInterface $processor): self
    {
        $this->processors[] = $processor;
        return $this;
    }
    
    public function getProcessorForFile(UploadedFile $file): FileProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($file)) {
                return $processor;
            }
        }
        
        throw new InvalidArgumentException('No processor found for file type: ' . $file->getMimeType());
    }
    
    public function process(UploadedFile $file): array
    {
        $processor = $this->getProcessorForFile($file);
        return $processor->process($file);
    }
}