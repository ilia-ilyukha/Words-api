<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\spin;
use function \Laravel\Prompts\text;
use App\Ai\Tools\Tool;

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
                    info('Function call: ' . $call['function']['name'] . ' with arguments: ' . json_encode($call['function']['arguments']));
                    
                    foreach ($this->tools() as $tool) {
                        if ($tool->definition()['function']['name'] === $call['function']['name']) {
                            $result = $tool->use(json_decode($call['function']['arguments'], associative: true));
                            $this->history[] = [
                                'role' => 'tool',
                                'tool_call_id' => $call['id'],
                                'content' => $result,
                                'type' => 'function_call_output',
                            ];
                        }
                    }
                    
                });
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
                // 'tools' => $this->tools(),
                'tools' => array_map(fn(Tool $tool) => $tool->definition(), $this->tools()),
                'tool_choice' => 'required',
                'stream' => false,
            ])
            ->throw()
            ->json();
    }

    public function tools()
    {
        return [
            new \App\Ai\Tools\CurrentTime(),
            new \App\Ai\Tools\ReadFile(),
        ];
    }
}
