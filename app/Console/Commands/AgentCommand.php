<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\spin;
use function \Laravel\Prompts\text;
use App\Ai\Tools\Tool;
use App\Ai\Agents\ChatbotAgent;
use App\Ai\Agents\GrammarAssistantAgent;

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
        // $agent = new GrammarAssistantAgent();
        $agent = new ChatbotAgent();

        while (true) {
            $prompt = text('What is on your mind?', required: true);

            $response = spin(
                fn() => $agent->prompt($prompt),
                'Thinking...',
            );
        }
    }

    public function get_current_time()
    {
        return now()->toIso8601String();
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

                info('Function result: ' .  (string)$result);
            }
        }
    }
}
