<?php

namespace App\Services\ExternalSubmission;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ExternalSubmissionClient
{
    private string $baseUrl;

    private string $token;

    private int $timeout;

    private int $connectTimeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('external_submission.url', ''), '/');
        $this->token = config('external_submission.token', '');
        $this->timeout = config('external_submission.timeout', 30);
        $this->connectTimeout = config('external_submission.connect_timeout', 10);
    }

    public function sendSubmission(array $data, array $files = []): Response
    {
        $request = $this->buildRequest();

        $payload = [
            'uuid' => $data['uuid'],
            'client_data' => $data['client_data'],
            'project_data' => $data['project_data'],
            'answers_data' => $data['answers_data'],
            'files_data' => $data['files_data'] ?? [],
        ];

        if (! empty($files)) {
            foreach ($files as $index => $file) {
                $payload["files[{$index}]"] = $this->prepareFileForUpload($file);
            }
        }

        return $request->post($this->baseUrl.'/api/submissions', $payload);
    }

    public function isConfigured(): bool
    {
        return ! empty($this->baseUrl) && ! empty($this->token);
    }

    private function buildRequest(): PendingRequest
    {
        return Http::withToken($this->token)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->attach(
                'headers',
                json_encode([
                    'Accept' => 'application/json',
                    'Content-Type' => 'multipart/form-data',
                ]),
                'headers.json'
            );
    }

    private function prepareFileForUpload(array $fileData): UploadedFile
    {
        $storedPath = $fileData['stored_path'] ?? '';
        $originalName = $fileData['original_name'] ?? 'file';

        $fullPath = Storage::disk(config('external_submission.file_disk', 'local'))->path($storedPath);

        return new UploadedFile(
            $fullPath,
            $originalName,
            $fileData['mime_type'] ?? null,
            null,
            true
        );
    }
}
