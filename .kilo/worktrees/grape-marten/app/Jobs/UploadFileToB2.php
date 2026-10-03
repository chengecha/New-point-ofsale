<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\StoredFile;
use Illuminate\Support\Facades\Log;



class UploadFileToB2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filePath;
    public $fileName;
    public $directory;
    public $userId;

    public function __construct($filePath, $fileName, $directory = 'uploads', $userId = null)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->directory = $directory;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        try {
            $fullPath = $this->directory . '/' . $this->fileName;
            
            Log::info("Starting B2 upload for: {$fullPath}");
            
            $fileContents = file_get_contents($this->filePath);
            
            $uploaded = Storage::disk('b2')->put($fullPath, $fileContents);
            
            if ($uploaded) {
                Log::info("Successfully uploaded to B2: {$fullPath}");
                
                // Update file record status
                if ($this->userId) {
                    StoredFile::where('filename', $this->fileName)
                        ->where('user_id', $this->userId)
                        ->update([
                            'status' => 'completed',
                            'uploaded_at' => now()
                        ]);
                }
                
                // Delete local temporary file
                if (file_exists($this->filePath)) {
                    unlink($this->filePath);
                    Log::info("Deleted local temp file: {$this->filePath}");
                }
                
            } else {
                Log::error("Failed to upload file to B2: {$fullPath}");
                $this->updateFileStatus('failed');
                $this->fail('Failed to upload file to B2');
            }
            
        } catch (\Exception $e) {
            Log::error("B2 Upload Error: " . $e->getMessage());
            $this->updateFileStatus('failed');
            $this->fail($e->getMessage());
        }
    }

    private function updateFileStatus($status): void
    {
        if ($this->userId) {
            StoredFile::where('filename', $this->fileName)
                ->where('user_id', $this->userId)
                ->update(['status' => $status]);
        }
    }
}