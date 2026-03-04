<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWordRequest;
use App\Services\WordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xml,csv|max:4096',
            ]);

            $file = $request->file('file');
            $path = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');

            // Read and parse XML data
            $xmlData = $this->getDataFromXml($path);

            // Save data to the database
            $wordService = new WordService();
            $dbResults = $wordService->prepareForStoreXML($xmlData);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully!',
                'file_path' => $path,
                'data' => $dbResults
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File upload failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    //TODO: Create CLass for XML and move these methods there ???
    public function getDataFromXml($filePath)
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
        // dd($xml);
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
