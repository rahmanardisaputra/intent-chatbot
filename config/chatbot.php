<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi AI & 9Router
    |--------------------------------------------------------------------------
    */
    'api_key' => env('NINEROUTER_API_KEY', ''),
    'base_url' => env('NINEROUTER_BASE_URL', 'https://api.openai.com/v1'),
    'model' => env('NINEROUTER_MODEL', 'gpt-4o-mini'),

    'system_prompt' => env(
        'CHATBOT_SYSTEM_PROMPT',
        'Kamu adalah asisten AI yang ramah, sopan, dan solutif. Gunakan tools/actions yang tersedia jika pengguna menanyakan informasi atau data tertentu.'
    ),

    /*
    |--------------------------------------------------------------------------
    | Action / Function Registry
    |--------------------------------------------------------------------------
    | Daftarkan semua class Action yang boleh dipanggil oleh AI di sini.
    | Cukup buat file class baru turunan App\Chatbot\Action dan daftarkan di sini.
    */
    'actions' => [
        \App\Chatbot\Actions\GetStudentCountAction::class,
    ],
];
