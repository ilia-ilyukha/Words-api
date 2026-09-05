<?php

namespace App\Ai\Tools;

class ReadFile implements Tool
{
    public function definition()
    {
        return [
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
        ];
    }

    public function use(array $arguments = [])
    {
        return file_get_contents(
            base_path($arguments['path'])
        );
    }
}
