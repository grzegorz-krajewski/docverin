<?php

namespace App\Services;

use GuzzleHttp\Client;
use RuntimeException;

class EmbeddingService
{
    public function embed(string $text, string $inputType = 'document'): array
    {
        $results = $this->embedMany([$text], $inputType);

        if (empty($results[0])) {
            throw new RuntimeException('Voyage returned an empty embedding.');
        }

        return $results[0];
    }

    public function embedMany(array $texts, string $inputType = 'document'): array
    {
        $apiKey = config('services.voyage.api_key');
        $baseUrl = rtrim((string) config('services.voyage.base_url'), '/');
        $model = (string) config('services.voyage.model');

        if (! $apiKey) {
            throw new RuntimeException('VOYAGE_API_KEY is not configured.');
        }

        if ($baseUrl === '' || $model === '') {
            throw new RuntimeException('Voyage configuration is incomplete.');
        }

        $texts = array_values(array_filter(
            array_map(fn ($text) => trim((string) $text), $texts),
            fn ($text) => $text !== ''
        ));

        if (empty($texts)) {
            throw new RuntimeException('Cannot embed empty text batch.');
        }

        $client = new Client([
            'base_uri' => $baseUrl . '/',
            'timeout' => 120,
        ]);

        $response = $client->post('embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'input' => $texts,
                'input_type' => $inputType,
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);
        $items = $data['data'] ?? null;

        if (! is_array($items) || empty($items)) {
            throw new RuntimeException('Voyage returned an empty embeddings batch.');
        }

        $embeddings = [];

        foreach ($items as $item) {
            $embedding = $item['embedding'] ?? null;

            if (! is_array($embedding) || empty($embedding)) {
                throw new RuntimeException('Voyage returned an invalid embedding in batch.');
            }

            $embeddings[] = array_map('floatval', $embedding);
        }

        return $embeddings;
    }
}