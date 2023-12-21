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

Route::get('/', [OpenAIController::class, 'index']);
Route::post('/submit-message', [OpenAIController::class, 'submitMessage']);
Route::delete('/delete-thread', [OpenAIController::class, 'deleteThread']);
Route::delete('/delete-assistant', [OpenAIController::class, 'deleteAssistant']);
Route::post('/create-new-thread', [OpenAIController::class, 'createNewThread']);
Route::post('/create-new-assistant', [OpenAIController::class, 'createNewAssistant']);
