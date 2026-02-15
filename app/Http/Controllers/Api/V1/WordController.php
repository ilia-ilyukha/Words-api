<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\WordFilter;
use App\Http\Resources\V1\WordResource;
use App\Models\Word;
use Illuminate\Http\Request;

class WordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(WordFilter $filters)
    {
        $words = Word::filter($filters)->get();
        return WordResource::collection($words);
    }

}
