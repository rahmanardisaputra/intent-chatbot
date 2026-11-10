<?php

use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ChatbotController::class, 'index'])->name('chatbot.index');
Route::post('/api/chat', [ChatbotController::class, 'send'])->name('chatbot.send');
