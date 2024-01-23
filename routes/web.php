<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpenAIController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Default Breeze route for welcome page


// Original OpenAIController routes
Route::get('/', [OpenAIController::class, 'index']);
Route::post('/submit-message', [OpenAIController::class, 'submitMessage']);
Route::post('/delete-thread/{threadId}', [OpenAIController::class, 'deleteThread']);
Route::post('/cancel-run', [OpenAIController::class, 'cancelRun']);


Route::post('/delete-assistant', [OpenAIController::class, 'deleteAssistant']);
Route::post('/create-new-thread', [OpenAIController::class, 'createNewThread']);
Route::post('/create-new-assistant', [OpenAIController::class, 'createNewAssistantWithCsv']);
Route::post('/start-run', [OpenAIController::class, 'startRun']);
Route::post('/check-run-status', [OpenAIController::class, 'checkRunStatus']);
Route::get('/get-messages', [OpenAIController::class, 'getMessages']);
Route::get('/download-file/{fileId}', [OpenAIController::class, 'downloadMessageFile']);
Route::get('/get-threads', [OpenAIController::class, 'getThreads']);

// Default Breeze routes for dashboard and user profile
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Include the Breeze auth routes
require __DIR__.'/auth.php';
