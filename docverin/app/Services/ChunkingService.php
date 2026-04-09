<?php

namespace App\Services;

class ChunkingService
{
    public function chunkText(string $text, int $chunkSize = 1200, int $overlap = 200): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $chunks = [];
        $length = mb_strlen($text);
        $start = 0;
        $index = 0;

        while ($start < $length) {
            $slice = mb_substr($text, $start, $chunkSize);
            $slice = trim($slice);

            if ($slice !== '') {
                $chunks[] = [
                    'chunk_index' => $index,
                    'content' => $slice,
                    'character_count' => mb_strlen($slice),
                ];

                $index++;
            }

            if (($start + $chunkSize) >= $length) {
                break;
            }

            $start += ($chunkSize - $overlap);
        }

        return $chunks;
    }
}