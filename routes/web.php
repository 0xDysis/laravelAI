<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenAIController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/assistant', [OpenAIController::class, 'index']);
Route::post('/submit-message', [OpenAIController::class, 'submitMessage']);
Route::post('/delete-thread', [OpenAIController::class, 'deleteThread']);
Route::post('/delete-assistant', [OpenAIController::class, 'deleteAssistant']);
