<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WordResource extends JsonResource
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
            'type' => 'words',
            'id' => $this->id,
            'attributes' => [
                'DE' => $this->DE,
                // 'description' => $this->when(
                //     !$request->routeIs([
                //         'tickets.index',
                //         'authors.tickets.index'
                //     ]),
                //     $this->description
                // ),
                'RU' => $this->RU,
                'words_capital_id' => $this->words_capital_id,
            ],
           
        ];
    }
}
