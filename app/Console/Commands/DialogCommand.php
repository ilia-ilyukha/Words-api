<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\spin;
use function \Laravel\Prompts\text;

class DialogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:dialog-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converse with an AI';

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

            $response = spin(
                fn() => $response = $this->runModel(),
                'Thinking...',
            );

            $this->history[] = $response['choices'][0]['message'];
            // $this->history = [...$this->history, ...$response['choices'][0]['message']];
          dump(
            $response,
            $this->history
            );
            $this->info($response['choices'][0]['message']['content']);
        }
    }

    public function runModel()
    {
        return Http::withToken(config('services.openai.key'))
            ->post(config('services.openai.url'), [
                'model' => config('services.openai.model'),
                'messages' => $this->history,
            ])
            ->throw()
            ->json();
    }
}
