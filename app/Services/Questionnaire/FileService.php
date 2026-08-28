<?php

namespace App\Services\Questionnaire;

use App\Models\ProjectFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function storeUploadedFile(UploadedFile $file, int $projectId): ProjectFile
    {
        $path = $file->store('project-files/'.$projectId, 'local');

        return ProjectFile::create([
            'project_id' => $projectId,
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function storeAnswerFile(UploadedFile $file, int $projectId): string
    {
        return $file->store('project-files/'.$projectId.'/answers', 'local');
    }

    public function getForProject(int $projectId): Collection
    {
        return ProjectFile::where('project_id', $projectId)->get();
    }

    public function delete(int $fileId): bool
    {
        $file = ProjectFile::find($fileId);

        if (! $file) {
            return false;
        }

        Storage::disk('local')->delete($file->stored_path);
        $file->delete();

        return true;
    }

    public function deleteForProject(int $projectId): void
    {
        $files = $this->getForProject($projectId);

        foreach ($files as $file) {
            Storage::disk('local')->delete($file->stored_path);
        }

        ProjectFile::where('project_id', $projectId)->delete();
    }
}
