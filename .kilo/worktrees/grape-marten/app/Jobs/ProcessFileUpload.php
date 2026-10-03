<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;
use App\Models\StoredFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tempPath;
    public $fileName;
    public $directory;
    public $userId;

    public function __construct(string $tempPath, string $fileName, string $directory, string $userId )
    {
        $this->tempPath = $tempPath;
        $this->fileName = $fileName;
        $this->directory = $directory;
        $this->userId = $userId;
    }

    public function handle(): void
    {
    
    try {
        Log::info("Processing file upload: {$this->fileName}");


        $fullTempPath = storage_path('app/private/' . $this->tempPath);

        // Validate file exists
        if (!file_exists($fullTempPath)) {
            Log::error("Temp file missing: $fullTempPath");
            return;
        }

        // Log details
        Log::info("Temporary file found", [
            'temp_path'   => $this->tempPath,
            'full_path'   => $fullTempPath,
            'file_exists' => true,
            'file_size'   => filesize($fullTempPath),
            'mime_type'   => mime_content_type($fullTempPath),
        ]);

        // Store database record first
        $storedFile = StoredFile::create([
            'user_id'   => $this->userId,
            'filename'  => $this->fileName,
            'path'      => "{$this->directory}/{$this->fileName}",
            'size'      => filesize($fullTempPath),
            'mime_type' => mime_content_type($fullTempPath),
            'folder'   => $this->directory,
            'status'    => 'processing',
        ]);

        // --- Upload to B2 ---
        $b2Path = "{$this->directory}/{$this->fileName}";
        $fileBytes = file_get_contents($fullTempPath);

        $uploaded = Storage::disk('b2')->put($b2Path, $fileBytes);

         if (!$uploaded) {
            throw new \Exception("B2 upload failed for: {$b2Path}");
         }

         Log::info("Uploaded to B2", [
             'b2_path' => $b2Path
         ]);

        // Delete temp file
        unlink($fullTempPath);

        // Update database
        $storedFile->update([
            'status' => 'completed',
             'uploaded_at' => now()
        ]);

        Log::info("File upload completed", [
            'filename' => $this->fileName,
            'b2_path'  => $b2Path
        ]);

    } catch (\Exception $e) {
        Log::error("ProcessFileUpload Error: " . $e->getMessage(), [
            'file' => $this->fileName,
            'temp_path' => $this->tempPath
        ]);
        $this->fail($e);
    }

    }
}
