<?php

use App\Http\Controllers\ChatHistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Chat History Routes
    Route::prefix('chat-history')->group(function () {
        // Get all chat histories (with optional user_id and thread_id filters)
        Route::get('/', [ChatHistoryController::class, 'index']);
        
        // Create a new chat history
        Route::post('/', [ChatHistoryController::class, 'store']);
        
        // Get chat history by ID
        Route::get('/{id}', [ChatHistoryController::class, 'show']);
        
        // Get chat history by thread ID
        Route::get('/thread/{threadId}', [ChatHistoryController::class, 'findByThreadId']);
        
        // Update chat history
        Route::put('/{id}', [ChatHistoryController::class, 'update']);
        
        // Append a message to a chat thread
        Route::post('/thread/{threadId}/message', [ChatHistoryController::class, 'appendMessage']);
        
        // Clear messages in a thread
        Route::delete('/thread/{threadId}/clear', [ChatHistoryController::class, 'clearThread']);
        
        // Delete a chat history
        Route::delete('/{id}', [ChatHistoryController::class, 'destroy']);
    });
});
