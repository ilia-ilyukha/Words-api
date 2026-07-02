<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    public function processResponse($response, string $text): array
    {
        $this->logIfEmptyContent($response);
        
        $results = $this->extractContent($response);
        $results = explode("|", $results);
        
        return $this->formatResponse($text, $results);
    }
    
    private function logIfEmptyContent($response): void
    {
        $json = $response->json();
        
        if (isset($json['choices'][0]['message']['content']) && 
            !$json['choices'][0]['message']['content']) {
            Log::info('Received response from OpenRouter', [
                'response' => $json
            ]);
        }
    }
    
    private function extractContent($response): string
    {
        return $response->json()['choices'][0]['message']['content'] ?? 'No response';
    }
    
    private function formatResponse(string $text, array $results): array
    {
        return [
            'type' => 'sentences',
            'word' => $text,
            'sentences' => [
                "B2" => [
                    'DE' => $results[0] ?? '',
                    'RU' => $results[1] ?? '',
                ],
            ],
        ];
    }
}