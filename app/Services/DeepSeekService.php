<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    private string $apiKey;

    private string $baseUrl;

    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.deepseek.api_key', '');
        $this->baseUrl = config('services.deepseek.base_url', 'https://api.deepseek.com');
        $this->model = config('services.deepseek.model', 'deepseek-chat');
    }

    /**
     * Send a chat completion request to DeepSeek.
     *
     * @param  string|array  $messages  A string (user prompt) or array of message objects
     * @param  array  $options  Optional parameters (temperature, max_tokens, etc.)
     * @return array The API response decoded as an array
     */
    public function chat(string|array $messages, array $options = []): array
    {
        if (is_string($messages)) {
            $messages = [
                ['role' => 'user', 'content' => $messages],
            ];
        }

        $payload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
        ], $options);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(120)->post("{$this->baseUrl}/v1/chat/completions", $payload);

        if ($response->failed()) {
            Log::error('DeepSeek API error', [
                'status' => $response->status(),
            ]);

            throw new \RuntimeException("DeepSeek API request failed with status {$response->status()}");
        }

        return $response->json();
    }

    /**
     * Send a chat request and return only the assistant's reply text.
     */
    public function ask(string $prompt, array $options = []): string
    {
        $response = $this->chat($prompt, $options);

        return $response['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Send a multi-turn conversation and return the assistant's reply text.
     */
    public function conversation(array $messages, array $options = []): string
    {
        $response = $this->chat($messages, $options);

        return $response['choices'][0]['message']['content'] ?? '';
    }

    /**
     * List available models.
     */
    public function listModels(): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
        ])->get("{$this->baseUrl}/v1/models");

        return $response->json()['data'] ?? [];
    }
}
