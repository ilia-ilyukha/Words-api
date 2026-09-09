<?php

namespace App\Ai\Agents;

use App\Ai\Tools\Tool;
use Illuminate\Support\Facades\Http;

class Agent
{
    protected array $history = [];

    /**
     * Get the instructions for the agent. 
     * Used in requests to the AI model.
     *
     * @return string
     */
    public function instructions(): string
    {
        // TODO: In qwen:3 there are no available instructions =(((.
        return 'You are helpful AI assistant. You can use tools to get information and answer questions. Use the tools when necessary.';
    }

    protected function tools(): array
    {
        return [];
    }

    protected function schema(): array
    {
        return [];
    }

    public function prompt(string $prompt)
    {
        $this->history = [
            [
                'role' => 'user',
                'content' => $prompt,
            ]
        ];

        while (true) {
            $response =  $this->runModel();

            $message = $response['choices'][0]['message'];
            $this->history[] = $message;

            $toolCalls = collect($response['choices'][0]['message']['tool_calls'] ?? [])
                ->filter(fn($item) => $item['type'] === 'function');

            if ($toolCalls->isEmpty()) {
                return $message['content'];
            }

            $toolCalls->each(function (array $call) {
                $this->runTool($call);
            });
        }
    }

    public function messages()
    {
        return $this->history;
    }

    /**
     * Run the model with the current history and tools.
     *
     * @return array
     */
    protected function runModel()
    {
        // return Http::withToken(config('services.openai.key'))
        //     ->timeout(300)
        //     ->post(config('services.openai.url'), [
        //         'model' => config('services.openai.model'),
        //         'messages' => $this->history,
        //         'tools' => array_map(fn(Tool $tool) => $tool->definition(), $this->tools()),
        //         'tool_choice' => 'required',
        //         'stream' => false,
        //         'text' => [
        //             'format' => [
        //                 'type' => 'json_schema',
        //                 'name' => 'assistant_response',
        //                 'strict' => true,
        //                 'schema' => $this->schema(),
        //             ]
        //         ]
        //     ])
        //     ->throw()
        //     ->json();

        return Http::withToken(config('services.openai.key'))
            ->timeout(300)
            ->post(config('services.openai.url'), [
                'model' => config('services.openai.model'),
                'messages' => $this->history,
                'think' => false,
                'tools' => array_map(fn(Tool $tool) => $tool->definition(), $this->tools()),
                'tool_choice' => 'auto', // или 'none', или конкретная функция

                // 'format' => $this->schema(), //This paramets works for Ollama qweb:3 models.
                // 'response_format' => [
                //     'type' => 'json_schema',
                //     'json_schema' => [
                //         'name' => 'assistant_response',
                //         'strict' => true,
                //         'schema' => $this->schema(),
                //     ],
                // ],
            ])
            ->throw()
            ->json();
    }

    /**
     * Run the specified tool based on the function call.
     *
     * @param array $call
     * @return void
     */
    public function runTool(array $call)
    {
        foreach ($this->tools() as $tool) {
            if ($tool->definition()['function']['name'] === $call['function']['name']) {
                $result = $tool->use(json_decode($call['function']['arguments'], associative: true));

                $this->history[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'],
                    'content' => (string)$result,
                    'type' => 'function_call_output',
                ];
            }
        }
    }
}
