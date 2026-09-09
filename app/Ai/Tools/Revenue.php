<?php

namespace App\Ai\Tools;

class Revenue implements Tool
{
    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'site_revenue',
                'description' => 'Get the current revenue data',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'period' => [
                            'type' => 'string',
                            'enum' => ['daily', 'monthly', 'quarterly', 'yearly'],
                            'description' => 'The period for which to retrieve revenue data (e.g., "daily", "monthly", "yearly").',
                        ],
                    ],
                ],
            ],
            'strict' => true,
        ];
    }
    public function use(array $arguments = []): string
    {
        $period = $arguments['period'] ?? 'daily';

        return [
            'daily' => 900,
            'monthly' => 27000,
            'quarterly' => 81000,
            'yearly' => 324000,
        ][$period] ?? 0;
    }
}
