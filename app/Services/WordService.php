<?php

namespace App\Services;

use App\Http\Requests\Api\V1\StoreWordRequest;
use App\Http\Resources\V1\WordResource;
use App\Models\Word;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class WordService
{
    public function prepareForStoreXML($xmlData)
    {
        $results = DB::transaction(function () use ($xmlData) {
            $words = [];
            $wordService = new WordService();

            foreach ($xmlData['word'] as $wordData) {
                $storeWordRequest = new StoreWordRequest([
                    'DE' => $wordData['de'] ?? null,
                    'RU' => $wordData['ru'] ?? null,
                    'words_capital_id' => (int)$xmlData['@words_capital'] ?? 0,
                ]);

                $words[] = $wordService->store($storeWordRequest);
            }

            return $words;
        });
        return $results;
    }
    /**
     * Create a new word resource
     */
    public function store(StoreWordRequest $request)
    {
        //TODO: Do not works, for some reason
        // $request->validated($request->all());
        try {
            // //policy
            // $this->isAble('store', Word::class);

            return new WordResource(Word::create([
                'DE' => $request->input('DE'),
                'RU' => $request->input('RU'),
                'words_capital_id' => $request->input('words_capital_id'),
            ]));
            // return new WordResource(Word::create($request->mappedAttributes()));
        } catch (AuthorizationException $ex) {
            return response()->json([
                'message' => 'You are not authorize to update this resource',
                'status' => 401
            ], 401);
        }
    }

    /**
     * Read a word resource by ID
     */
    public function read(string $id): ?array
    {
        // Fetch from database or cache
        return null;
    }

    /**
     * Update a word resource
     */
    public function update(string $id, array $data): bool
    {
        // Update logic here
        return true;
    }

    /**
     * Delete a word resource
     */
    public function delete(string $id): bool
    {
        // Delete logic here
        return true;
    }

    /**
     * Get all words
     */
    public function getAll(): array
    {
        // Fetch all words from database
        return [];
    }

    /**
     * Search words by keyword
     */
    public function search(string $keyword): array
    {
        // Search logic here
        return [];
    }
}
