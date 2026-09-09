<?php

namespace App\Ai\Agents;

use Override;

class GrammarAssistantAgent extends Agent
{
    public function instructions(): string
    {
        return 'You are a grammar assistant. You will receive text and your task is to correct any grammatical errors, improve sentence structure, and enhance clarity while maintaining the original meaning. Provide the corrected text in your response.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            // 'properties' => [
            //     'nouns' => ['type' => 'string'],
            //     'adjectives' => ['type' => 'string'],
            //     'verbs' => ['type' => 'string'],
            //     'response' => [
            //         'type' => 'string',
            //         'description' => 'The content of the assistant\'s response.',
            //     ],
            // ],

            // This is format for Ollama qweb:3 models.
            'format' => [
                'type' => 'object',
                'properties' => [
                    'nouns' => ['type' => 'string'],
                    'adjectives' => ['type' => 'string'],
                    'verbs' => ['type' => 'string'],
                ],
            ],
            'required' => ['nouns', 'adjectives', 'verbs'],
            'additionalProperties' => false,
        ];
    }
}