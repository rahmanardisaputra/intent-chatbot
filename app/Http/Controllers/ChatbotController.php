<?php

namespace App\Http\Controllers;

use App\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    public function index(): View
    {
        return view('chatbot');
    }

    public function send(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        try {
            $response = $chatbot->chat(
                $validated['message'],
                $validated['history'] ?? []
            );

            return response()->json([
                'success' => true,
                'reply' => $response['reply'],
                'history' => $response['history'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'reply' => 'Terjadi kendala: ' . $e->getMessage(),
                'history' => $request->input('history', []),
            ], 500);
        }
    }
}
