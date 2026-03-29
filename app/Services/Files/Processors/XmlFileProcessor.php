<?php

namespace App\Services\Files\Processors;

use App\Services\Files\Processors\BaseFileProcessor;
use App\Services\WordService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class XmlFileProcessor extends BaseFileProcessor
{
    protected array $supportedMimeTypes = [
        'application/xml',
        'text/xml',
        'application/x-xml',
    ];

    public function __construct(
        private WordService $wordService
    ) {}

    public function process(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());
        $xml = simplexml_load_string($content);

        // Конвертируем XML в массив с нужной структурой
        $data = $this->xmlToArray($xml);


        // Save data to the database

        $dbResults = $this->wordService->prepareForStoreXML($data);
        // Валидируем структуру
        // if (!$this->validateStructure($data, $this->getExpectedStructure())) {
        //     throw new \Exception('Invalid XML structure');
        // }

        return $data;
    }

    //TODO: Create CLass for XML and move these methods there ???
    public function getExpectedStructure($filePath)
    {
        try {
            $xmlContent = Storage::disk('public')->get($filePath);

            // Suppress XML errors during parsing
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);

            if ($xml === false) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                throw new \Exception('Invalid XML: ' . json_encode($errors));
            }

            // Convert SimpleXML object to array
            $data = $this->xmlToArray($xml);

            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Error reading XML file: ' . $e->getMessage());
        }
    }

    private function xmlToArray($xml)
    {
        $result = [];
        $content = [];

        // Convert attributes
        foreach ($xml->attributes() as $key => $value) {
            $result['@' . $key] = (string) $value;
        }

        $result = $this->recursiveXmlToArray($xml);
        return $result;
    }

    private function recursiveXmlToArray($xml)
    {
        $result = [];
        $content = (string) $xml;

        // Convert attributes
        foreach ($xml->attributes() as $key => $value) {
            $result['@' . $key] = (string) $value;
        }

        // Convert child elements
        foreach ($xml->children() as $element) {
            $name = $element->getName();
            $childContent = $this->recursiveXmlToArray($element);

            if (!isset($result[$name])) {
                $result[$name] = $childContent;
            } else {
                // Handle multiple elements with the same name
                if (!is_array($result[$name]) || !isset($result[$name][0])) {
                    $result[$name] = [$result[$name]];
                }
                $result[$name][] = $childContent;
            }
        }

        // If the element has no children or attributes, return the content
        if (empty($result)) {
            return $content;
        }

        return $result;
    }
}
