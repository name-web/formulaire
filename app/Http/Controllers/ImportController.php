<?php

namespace App\Http\Controllers;

use App\Services\ExternalSubmission\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    public function __construct(
        private ImportService $importService,
    ) {}

    public function import(Request $request): JsonResponse
    {
        $secret = $request->header('X-External-Submission-Secret');
        $expectedSecret = config('external_submission.import_secret');

        if (empty($expectedSecret) || $secret !== $expectedSecret) {
            Log::warning('Import request rejected: invalid secret.', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'submission_uuid' => ['required', 'string', 'uuid'],
            'client_data' => ['required', 'array'],
            'client_data.name' => ['required', 'string'],
            'client_data.phone' => ['nullable', 'string'],
            'project_data' => ['required', 'array'],
            'project_data.project_types' => ['required', 'array', 'min:1'],
            'answers_data' => ['nullable', 'array'],
            'files_data' => ['nullable', 'array'],
        ]);

        $result = $this->importService->import(
            $request->input('submission_uuid'),
            $request->input('client_data'),
            $request->input('project_data'),
            $request->input('answers_data'),
            $request->input('files_data', []),
        );

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'] ?? 'Import successful',
                'project_id' => $result['project_id'] ?? null,
            ], $result['code'] ?? 200);
        }

        return response()->json(['error' => $result['error']], $result['code'] ?? 500);
    }
}
