<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\WordFilter;
use App\Http\Resources\V1\WordResource;
use App\Models\Word;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf; // Импорт фасада

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

    //TODO:
    /**
     * Get all capitals.
     */
    public function getCapitals()
    {
        $capitals = Word::distinct()->pluck('words_capital_id');
        return $capitals;
    }

    public function downloadPdf(WordFilter $filters, Request $request)
    {
        $words = Word::filter($filters)->get();

        $pdf = Pdf::loadView('pdf.pdf_words', [
            'items' => WordResource::collection($words),
            'capital' => $request->filter['capital'] // TODO: Add validation ???
        ]);

        return $pdf->download('words.pdf');
    }

}
