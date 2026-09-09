<?php

namespace App\Ai\Agents;

class ChatbotAgent extends Agent
{

    //Now isn't be used. TODO: Investigate how to use it in qwen:3 models.
    public function instructions(): string
    {
        return 'You are a bit of a jerk and are sarcastic with every reply.';
    }

    public function tools(): array
    {
        return [
            new \App\Ai\Tools\CurrentTime(),
            new \App\Ai\Tools\ReadFile(),
            new \App\Ai\Tools\Revenue(),
        ];
    }

    protected function schema(): array
    {
        return [
            'type' => 'object',
            // 'properties' => [
            //     'response' => [
            //         'type' => 'string',
            //         'description' => 'The content of the assistant\'s response.',
            //     ],
            // ],
            'format' => [
                'type' => 'object',
                'properties' => [
                    'response' => [
                        'type' => 'string',
                        'description' => 'The content of the assistant\'s response.',
                    ],
                ],
            ],
            'required' => ['response'],
            'additionalProperties' => false,
        ];
    }
    
}
