<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\spin;
use function \Laravel\Prompts\text;

class ChatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:chat-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Receive an AI responce';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prompt = text('What is on your mind?', required: true);

        $response = spin(
            fn() => $response = $this->runModel($prompt),
            'Thinking...',
        );
        dump($response['choices'][0]['message']['content']);
    }

    public function runModel($prompt)
    {
        return Http::withToken(config('services.openai.key'))
            ->post(config('services.openai.url'), [
                'model' => config('services.openai.model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ])
            ->throw()
            ->json();
    }
}
