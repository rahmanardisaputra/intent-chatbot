<?php

namespace App\Chatbot;

use Illuminate\Support\Facades\Http;
use Exception;

class ChatbotService
{
    /**
     * Daftar Action yang terdaftar.
     *
     * @var array<string, Action>
     */
    protected array $actions = [];

    public function __construct()
    {
        $actionClasses = config('chatbot.actions', []);

        foreach ($actionClasses as $class) {
            if (class_exists($class)) {
                $instance = new $class();
                $this->actions[$instance->name()] = $instance;
            }
        }
    }

    /**
     * Kirim pesan ke AI dan lakukan Tool Calling jika diperlukan.
     */
    public function chat(string $userMessage, array $history = []): array
    {
        $apiKey = config('chatbot.api_key');
        $baseUrl = rtrim(config('chatbot.base_url', 'https://api.openai.com/v1'), '/');
        $model = config('chatbot.model', 'gpt-4o-mini');

        if (empty($apiKey)) {
            throw new Exception("API Key 9Router / AI belum diatur di file .env (NINEROUTER_API_KEY).");
        }

        // Susun riwayat pesan
        $messages = $history;
        if (empty($messages)) {
            $messages[] = [
                'role' => 'system',
                'content' => config('chatbot.system_prompt'),
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // Format tools yang didaftarkan
        $tools = array_values(array_map(
            fn(Action $action) => $action->toToolDefinition(),
            $this->actions
        ));

        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        // Request pertama ke AI
        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->post("{$baseUrl}/chat/completions", $payload);

        if (!$response->successful()) {
            throw new Exception("Error dari Provider AI ({$response->status()}): " . $response->body());
        }

        $botMessage = $response->json('choices.0.message');
        $messages[] = $botMessage;

        // Cek jika AI memutuskan memanggil tool / action
        if (!empty($botMessage['tool_calls'])) {
            foreach ($botMessage['tool_calls'] as $toolCall) {
                $funcName = $toolCall['function']['name'] ?? '';
                $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?: [];

                // Eksekusi action
                $result = isset($this->actions[$funcName])
                    ? $this->actions[$funcName]->handle($arguments)
                    : ['error' => "Action '{$funcName}' tidak ditemukan di sistem."];

                // Berikan hasil JSON data ke AI
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            }

            // Request kedua: Minta AI menarasikan hasil data JSON ke bahasa alami
            $followUp = Http::withToken($apiKey)
                ->timeout(60)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => $messages,
                ]);

            if (!$followUp->successful()) {
                throw new Exception("Error saat menerjemahkan data AI: " . $followUp->body());
            }

            $finalMessage = $followUp->json('choices.0.message');
            $messages[] = $finalMessage;

            return [
                'reply' => $finalMessage['content'] ?? 'Data berhasil didapatkan.',
                'history' => $messages,
            ];
        }

        return [
            'reply' => $botMessage['content'] ?? 'Tidak ada respons.',
            'history' => $messages,
        ];
    }
}
