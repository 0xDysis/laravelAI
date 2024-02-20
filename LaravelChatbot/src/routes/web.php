<?php

use Illuminate\Support\Facades\Route;
use LaravelAI\LaravelChatbot\Http\Controllers\OpenAIController;
use Illuminate\Support\Facades\Auth;

Route::middleware('auth')->group(function () {
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

});


