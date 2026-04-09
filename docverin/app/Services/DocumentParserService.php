<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Smalot\PdfParser\Parser;

class DocumentParserService
{
    public function extract(Document $document): string
    {
        $fullPath = Storage::disk('local')->path($document->file_path);

        if (! file_exists($fullPath)) {
            throw new RuntimeException('Document file not found at: ' . $fullPath);
        }

        $mimeType = strtolower((string) $document->mime_type);
        $extension = strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION));

        if (in_array($mimeType, ['text/plain', 'text/markdown']) || in_array($extension, ['txt', 'md'])) {
            return $this->extractPlainText($fullPath);
        }

        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            return $this->extractPdf($fullPath);
        }

        throw new RuntimeException(
            'Unsupported document type. MIME: ' . $mimeType . ' | EXT: ' . $extension
        );
    }

    protected function extractPlainText(string $path): string
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException('Unable to read document content.');
        }

        return trim($content);
    }

    protected function extractPdf(string $path): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = trim($pdf->getText());

            if ($text !== '') {
                return $text;
            }
        } catch (\Throwable $e) {
            if (! str_contains(strtolower($e->getMessage()), 'secured pdf')) {
                // dla innych błędów też i tak spróbujemy fallback
            }
        }

        return $this->extractPdfWithFallback($path);
    }

    protected function extractPdfWithFallback(string $path): string
    {
        $tmpDir = storage_path('app/tmp/pdf');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $decryptedPdf = $tmpDir . '/' . uniqid('pdf_', true) . '_decrypted.pdf';
        $textFile = $tmpDir . '/' . uniqid('pdf_', true) . '.txt';

        try {
            // 1) spróbuj zrzucić zabezpieczenia bez hasła
            $qpdfCommand = sprintf(
                'qpdf --decrypt %s %s 2>&1',
                escapeshellarg($path),
                escapeshellarg($decryptedPdf)
            );

            exec($qpdfCommand, $qpdfOutput, $qpdfExitCode);

            $sourcePdf = $qpdfExitCode === 0 && file_exists($decryptedPdf)
                ? $decryptedPdf
                : $path;

            // 2) wyciągnij tekst przez pdftotext
            $pdftotextCommand = sprintf(
                'pdftotext -layout %s %s 2>&1',
                escapeshellarg($sourcePdf),
                escapeshellarg($textFile)
            );

            exec($pdftotextCommand, $pdftotextOutput, $pdftotextExitCode);

            if ($pdftotextExitCode !== 0 || ! file_exists($textFile)) {
                throw new RuntimeException(
                    'Fallback PDF extraction failed: ' . implode("\n", array_merge($qpdfOutput, $pdftotextOutput))
                );
            }

            $text = trim((string) file_get_contents($textFile));

            if ($text === '') {
                throw new RuntimeException('Fallback PDF extraction returned empty text.');
            }

            return $text;
        } finally {
            if (file_exists($decryptedPdf)) {
                @unlink($decryptedPdf);
            }

            if (file_exists($textFile)) {
                @unlink($textFile);
            }
        }
    }
}