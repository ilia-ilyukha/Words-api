<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\FileUploadController;
use App\Http\Controllers\Api\V1\SentenceController;
use App\Http\Controllers\Api\V1\WordController;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(base_path('routes/api_v1.php'));
// Route::prefix('v2')->group(base_path('routes/api_v2.php'));


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::group(['prefix' => 'words'], function () {

    Route::get('/', [WordController::class, 'index']);
    Route::post('/', [WordController::class, 'store'])->name('words.store');;

    Route::get('/capitals', [WordController::class, 'getCapitals']);
    Route::get('/{id}', [WordController::class, 'show']);

    Route::apiResource('sentences', SentenceController::class);
});

Route::get('/generateSentencesForCapital', [WordController::class, 'generateSentencesForCapital']);

// Route::delete('/{id}', [WordController::class, 'destroy']);
Route::delete('/words', [WordController::class, 'destroyMultiple']);
Route::delete('/all', [WordController::class, 'destroyAll']);

Route::get('/downloadPdf', [WordController::class, 'downloadPdf']);

Route::post('/upload', [FileUploadController::class, 'upload'])->name('upload');
Route::get('/readText', [WordController::class, 'readText'])->name('readText');
Route::post('/translate', [WordController::class, 'translate'])->name('translate');

Route::post('/generateSentence', [WordController::class, 'generateSentence'])->name('generateSentence');

// Route::get('/', function() {
//     return response()->json([
//         'message' => 'Hello, API!'
//     ], 200);
// });

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
