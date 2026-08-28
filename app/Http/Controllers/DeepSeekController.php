<?php

namespace App\Http\Controllers;

use App\Services\DeepSeekService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeepSeekController extends Controller
{
    public function __construct(
        private DeepSeekService $deepseek
    ) {}

    /**
     * POST /api/deepseek/chat
     *
     * Body: { "message": "Hello" } or { "messages": [{"role":"user","content":"Hello"}] }
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required_without:messages|string',
            'messages' => 'required_without:message|array',
            'messages.*.role' => 'required|in:user,assistant,system',
            'messages.*.content' => 'required|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1',
        ]);

        $messages = $request->input('messages', $request->input('message'));

        $options = array_filter([
            'temperature' => $request->input('temperature'),
            'max_tokens' => $request->input('max_tokens'),
        ]);

        $response = $this->deepseek->chat($messages, $options);

        return response()->json($response);
    }

    /**
     * POST /api/deepseek/ask
     *
     * Body: { "prompt": "What is Laravel?" }
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1',
        ]);

        $options = array_filter([
            'temperature' => $request->input('temperature'),
            'max_tokens' => $request->input('max_tokens'),
        ]);

        $reply = $this->deepseek->ask($request->input('prompt'), $options);

        return response()->json([
            'reply' => $reply,
        ]);
    }
}
