<?php

namespace App\Services;

use GuzzleHttp\Client;
use RuntimeException;

class AnswerGenerationService
{
    public function generate(string $question, array $retrievedChunks): string
    {
        $apiKey = config('services.groq.api_key');
        $baseUrl = rtrim((string) config('services.groq.base_url'), '/');
        $model = (string) config('services.groq.model');

        if (! $apiKey) {
            throw new RuntimeException('GROQ_API_KEY is not configured.');
        }

        if ($baseUrl === '' || $model === '') {
            throw new RuntimeException('Groq configuration is incomplete.');
        }

        $context = $this->buildContext($retrievedChunks);

        $systemPrompt = <<<TEXT
You are a document question-answering assistant.

Answer strictly from the provided document excerpts.

Rules:
- Use only the provided context.
- If the answer is not present in the context, say: "The answer is not available in the provided documents."
- Do not guess.
- Do not invent facts.
- Keep the answer concise and precise.
- If multiple excerpts are relevant, combine them carefully.
TEXT;

        $userPrompt = <<<TEXT
Question:
{$question}

Context:
{$context}
TEXT;

        $client = new Client([
            'base_uri' => rtrim((string) config('services.groq.base_url'), '/') . '/',
            'timeout' => 60,
        ]);

        $response = $client->post('chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'temperature' => 0.2,
                'max_tokens' => 400,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);

        $text = $data['choices'][0]['message']['content'] ?? null;

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Groq returned an empty response.');
        }

        return trim($text);
    }

    protected function buildContext(array $retrievedChunks): string
    {
        $parts = [];

        foreach ($retrievedChunks as $index => $result) {
            $chunk = $result['chunk'];
            $score = $result['score'];

            $parts[] = sprintf(
                "[Source %d | score %.4f | document: %s | chunk: %d]\n%s",
                $index + 1,
                $score,
                $chunk->document->title,
                $chunk->chunk_index,
                $chunk->content
            );
        }

        return implode("\n\n", $parts);
    }
}