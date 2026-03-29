<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SentenceResource extends JsonResource
{
    // public static $wrap = 'word'; // Wrap container for response
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'sentences',
            'id' => $this->id,
            'word_id'  => $this->word_id,
            'description_DE' => $this->description_DE,
            'description_RU' => $this->description_RU,
        ];
    }
}
