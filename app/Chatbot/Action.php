<?php

namespace App\Chatbot;

abstract class Action
{
    /**
     * Nama unik fungsi yang dikenali oleh AI.
     */
    abstract public function name(): string;

    /**
     * Deskripsi fungsi: Kapan dan untuk apa AI harus memanggil ini.
     */
    abstract public function description(): string;

    /**
     * Parameter JSON Schema yang diharapkan dari AI.
     */
    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];
    }

    /**
     * Eksekusi logika / query database dan kembalikan data dalam bentuk array/object.
     */
    abstract public function handle(array $params): mixed;

    /**
     * Format definisi Tool sesuai standar OpenAI / 9router.
     */
    public function toToolDefinition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => $this->description(),
                'parameters' => $this->parameters(),
            ],
        ];
    }
}
