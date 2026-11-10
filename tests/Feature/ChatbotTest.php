<?php

namespace Tests\Feature;

use App\Chatbot\Actions\GetStudentCountAction;
use App\Chatbot\ChatbotService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    public function test_chatbot_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('AI Starter Pack');
    }

    public function test_student_count_action_returns_expected_data(): void
    {
        $action = new GetStudentCountAction();
        $this->assertEquals('get_student_count', $action->name());

        $resultRpl = $action->handle(['jurusan' => 'RPL']);
        $this->assertEquals(75, $resultRpl['total_siswa']);

        $resultAll = $action->handle([]);
        $this->assertEquals(320, $resultAll['total_siswa']);
    }

    public function test_chatbot_service_with_mocked_ai_tool_call(): void
    {
        config([
            'chatbot.api_key' => 'test-key',
            'chatbot.base_url' => 'https://api.openai.com/v1',
            'chatbot.model' => 'gpt-4o-mini',
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::sequence()
                // Response 1: AI memutuskan memanggil tool
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => null,
                                'tool_calls' => [
                                    [
                                        'id' => 'call_123',
                                        'type' => 'function',
                                        'function' => [
                                            'name' => 'get_student_count',
                                            'arguments' => json_encode(['jurusan' => 'RPL']),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
                // Response 2: AI memberikan kesimpulan bahasa manusia setelah dapat data
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'Saat ini ada 75 siswa di jurusan RPL ya!',
                            ],
                        ],
                    ],
                ]),
        ]);

        $service = new ChatbotService();
        $response = $service->chat('Ada berapa siswa jurusan RPL?');

        $this->assertEquals('Saat ini ada 75 siswa di jurusan RPL ya!', $response['reply']);
    }
}
