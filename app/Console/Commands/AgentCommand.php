<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\spin;
use function \Laravel\Prompts\text;

class AgentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:agent-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interact with an AI agent';

    /**
     * Execute the console command.
     */

    protected array $history = [];

    public function handle()
    {
        while (true) {
            $prompt = text('What is on your mind?', required: true);

            $this->history[] = [
                'role' => 'user',
                'content' => $prompt,
            ];

            while (true) {
                $response = spin(
                    fn() => $this->runModel(),
                    'Thinking...',
                );

                $message = $response['choices'][0]['message'];
                $this->history[] = $message;

                $functionCalls = collect($response['choices'][0]['message']['tool_calls'] ?? [])
                    ->filter(fn($item) => $item['type'] === 'function');

                if ($functionCalls->isEmpty()) {
                    $this->info($message['content']);

                    break;
                }

                $functionCalls->each(function (array $call) {
                    if ($call['function']['name'] === 'get_current_time') {
                        $currentTime = now()->toIso8601String();

                        $this->history[] = [
                            'role' => 'tool',
                            'tool_call_id' => $call['id'],
                            'content' => $currentTime,
                        ];
                    }
                    if ($call['function']['name'] === 'read_file') {

                        $this->history[] = [
                            'role' => 'tool',
                            'tool_call_id' => $call['id'],
                            'content' => file_get_contents(
                                base_path(json_decode($call['function']['arguments'])->path)
                            ),
                        ];
                    }
                });
                
                // if (
                //     isset($message['tool_calls']) &&
                //     $message['tool_calls'][0]['type'] === 'function'
                // ) {
                //     $call = $message['tool_calls'][0];
                //     // $content = $call['function']['name']();


                // }
            }
        }
    }

    public function get_current_time()
    {
        return now()->toIso8601String();
    }

    public function runModel()
    {
        return Http::withToken(config('services.openai.key'))
            ->timeout(300)
            ->post(config('services.openai.url'), [
                'model' => config('services.openai.model'),
                'messages' => $this->history,
                'tools' => [
                    [
                        'type' => 'function',
                        'function' => [
                            'name' => 'get_current_time',
                            'description' => 'Get the current server time as an ISO 8601 string',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => new \stdClass(),
                            ],
                        ],
                    ],
                    [
                        'type' => 'function',
                        'function' => [
                            'name' => 'read_file',
                            'description' => 'Read the contents of a file, relative to project root.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'path' => [
                                        'type' => 'string',
                                        'description' => 'The relative path to the file.',
                                    ],
                                    // 'required' => ['path'],
                                    // 'additionalProperties' => false,
                                ],
                            ],
                        ],
                    ]
                ],
                'tool_choice' => 'required',
                'stream' => false,
            ])
            ->throw()
            ->json();
    }
}
