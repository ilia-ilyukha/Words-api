<?php

namespace App\Services;

use App\Models\Sentence;
use Illuminate\Support\Facades\Log;

class SentenceService
{
    public function storeSentence($description_de, $description_ru, $word_id)
    {
        Log::info('Storing sentence for word', [
            'word' => $word_id,
            'description_de' => $description_de
        ]);
        $sentence = Sentence::create([
            'description_DE' => $description_de,
            'description_RU' => $description_ru,
            'word_id' => $word_id,
        ]);
        return $sentence;
    }
}
