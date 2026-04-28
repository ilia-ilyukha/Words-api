<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreSentenceRequest;
use App\Http\Resources\V1\SentenceResource;
use App\Models\Sentence;
use App\Services\SentenceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SentenceController extends ApiController
{
    public function __construct(
        private SentenceService $sentenceService
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSentenceRequest $request)
    {
        $sentence = $this->sentenceService->storeSentence(
            $request->description_de,
            $request->description_ru,
            $request->word_id
        );
        return new SentenceResource($sentence);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $sentence = Sentence::findOrFail($id);

            return new SentenceResource($sentence);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Sentence cannot be found', 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->sentenceService->delete($id);

            return $this->success('Sentence deleted successfully', 200);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Sentence cannot be found', 404);
        }
    }
}
