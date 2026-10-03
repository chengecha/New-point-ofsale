<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessFileUpload;
use App\Models\Directory;
use App\Models\folder_user_permissions;
use App\Models\StoredFile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FileController extends Controller
{
    public function upload(Request $request): JsonResponse
    {

        //check permission

        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
             'directory' => 'required','string','alpha_dash',Rule::exists(Directory::class, 'foldername'),
            //'directory' => 'sometimes|string|alpha_dash'
        ]);

        $perm = folder_user_permissions ::where('folder_id', $request->input('directory'))
                ->where('user_id', auth()->id());

             if($perm->can_upload != true){
                return response()->json([
                    'error' => 'You do not have permission to upload to this directory.'
                ], 403);
             }


        $file = $request->file('file');
        $directory = $request->input('directory', 'uploads');

        $directory = trim(strtolower($directory), "/");

        // Generate unique filename
        $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
        
        Log::info("Received upload request for: {$fileName}");

        $tempPath = $file->store('temp');

        // Dispatch job to process upload
        ProcessFileUpload::dispatch(
            $tempPath,
            $fileName, 
            $directory, 
            auth()->id()
            // auth()->id()
        );

        return response()->json([
            'message' => 'File upload processing in background',
            'filename' => $fileName,
            'directory' => $directory
        ], 202);
    }

    //PASS DIRECORY NAME AND LIST FILES IN THAT DIRECTORY
    public function listFiles(Request $request): JsonResponse
    {
        $directory = $request->input('directory'); //, 'test-uploads');

         $perm = folder_user_permissions ::where('folder_id', $request->input('directory'))
                ->where('user_id', auth()->id());

             if($perm->can_read != true){
                return response()->json([
                    'error' => 'You do not have permission to upload to this directory.'
                ], 403);
             }

        
        try {
            $files = Storage::disk('b2')->files($directory);
            
            $fileList = collect($files)->map(function ($file) {
                return [
                    'name' => basename($file),
                    'path' => $file,
                    'url' => Storage::disk('b2')->url($file),
                    'size' => Storage::disk('b2')->size($file),
                    'last_modified' => Storage::disk('b2')->lastModified($file),
                ];
            });
            
            return response()->json([
                'files' => $fileList,
                'directory' => $directory,
                'count' => count($files)
            ]);
            
        } catch (\Exception $e) {
            Log::error('B2 List Files Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to list files: ' . $e->getMessage()
            ], 500);
        }
    }


    public function download($filename,$directory): JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        try { 

              $perm = folder_user_permissions ::where('folder_id',$directory ) //$request->input('directory'))
                ->where('user_id', auth()->id());

             if($perm->can_read != true){
                return response()->json([
                    'error' => 'You do not have permission to download to this directory.'
                ], 403);
             }

            $path = 'test-uploads/' . $filename;
            
            if (!Storage::disk('b2')->exists($path)) {
                return response()->json(['error' => 'File not found'], 404);
            }
            
            return Storage::disk('b2')->download($path, $filename);
                
        } catch (\Exception $e) {
            Log::error('B2 Download Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Download failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDownloadUrl(Request $request): JsonResponse
    {
        try {

          $filename  = $request->input('filename');

          $directory  = $request->input('directory');


            $path ="{$directory}/.{$filename}";
            
            if (!Storage::disk('b2')->exists($path)) {
                return response()->json(['error' => 'File not found'], 404);
            }
            
            // Generate temporary URL (valid for 1 hour)
            $url = Storage::disk('b2')->temporaryUrl(
                $path, 
                now()->addHour()
            );
            
            return response()->json([
                'download_url' => $url,
                'expires_at' => now()->addHour()->toISOString(),
                'filename' => $filename
            ]);
            
        } catch (\Exception $e) {
            Log::error('B2 URL Generation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate download URL: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteFile($filename,$directory): JsonResponse
    {
        try { 
              $perm = folder_user_permissions ::where('folder_id',$directory ) //$request->input('directory'))
                ->where('user_id', auth()->id());

             if($perm->can_read != true){
                return response()->json([
                    'error' => 'You do not have permission to delete to this directory.'
                ], 403);
             }

            $path = 'uploads/' . $filename;
            
            if (!Storage::disk('b2')->exists($path)) {
                return response()->json(['error' => 'File not found'], 404);
            }
            
            Storage::disk('b2')->delete($path);
            
            // Also delete from database if exists
            StoredFile::where('filename', $filename)->delete();
            
            Log::info("File deleted: {$filename}");
            
            return response()->json([
                'message' => 'File deleted successfully',
                'filename' => $filename
            ]);
            
        } catch (\Exception $e) {
            Log::error('B2 Delete Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listUploadedFiles(): JsonResponse
    {
        try {
            $files = StoredFile::with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'filename' => $file->filename,
                        'path' => $file->path,
                        'size' => $file->size,
                        'mime_type' => $file->mime_type,
                        'status' => $file->status,
                        'uploaded_at' => $file->uploaded_at,
                        'created_at' => $file->created_at,
                        'user' => $file->user ? $file->user->name : null,
                    ];
                });

            return response()->json([
                'files' => $files,
                'count' => $files->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Database Files List Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to list uploaded files: ' . $e->getMessage()
            ], 500);
        }
    }
} 



//  public function upload(Request $request): JsonResponse
//     {
//         $request->validate([
//             'file' => 'required|file|max:102400', // 100MB max
//             'directory' => 'sometimes|string|alpha_dash'
//         ]);
//         $file = $request->file('file');
//         $directory = $request->input('directory', 'uploads');
//         // Generate unique filename
//         $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
//         $tempPath = $file->store('temp');
//         $fullTempPath = storage_path('app/private/' . $tempPath);//$this->tempPath);
//     // Validate file exists
//     if (!file_exists($fullTempPath)) {
//         //Log::error("Temp file missing: $fullTempPath");
//         return response()->json([
//             'error' => 'Temporary file not found on server.',
//              'file_path' => $fullTempPath
//         ], 500);
//     }
//     // Log details
//     Log::info("📁 Temporary file found", [
//         'temp_path'   =>  $tempPath,//$this->tempPath,
//         'full_path'   => $fullTempPath,
//         'file_exists' => true,
//         'file_size'   => filesize($fullTempPath),
//         'mime_type'   => mime_content_type($fullTempPath),
//     ]);

//     // Store database record first
//     $storedFile = StoredFile::create([
//         'filename'  => $fileName, //$this->fileName,
//         'path'      => "{$directory} / {$fileName}", //"{$this->directory}/{$this->fileName}",
//         'size'      => filesize($fullTempPath),
//         'mime_type' => mime_content_type($fullTempPath),
//         'status'    => 'processing',
//     ]);

//     // --- Upload to B2 ---
//     $b2Path = "{$directory} / {$fileName}";//"{$this->directory}/{$this->fileName}";
//     $fileBytes = file_get_contents($fullTempPath);

//     $uploaded = Storage::disk('b2')->put($b2Path, $fileBytes);

//     if (!$uploaded) {
//         throw new \Exception("B2 upload failed for: {$b2Path}");
//     }

//     Log::info("Uploaded to B2", [
//         'b2_path' => $b2Path
//     ]);

//     // Delete temp file
//     unlink($fullTempPath);

//     // Update database
//     $storedFile->update([
//         'status' => 'uploaded'
//     ]);





//         // Dispatch job to process uplod
//         // ProcessFileUpload::dispatch(
//         //     $tempPath,
//         //     //$file, 
//         //     $fileName, 
//         //     $directory, 
//         //     //auth()->id() // if using authentication
//         // );

//         Log::info("Received upload request for: {$fileName}");


//         return response()->json([
//             'message' => 'File upload processing in background',
//             'filename' => $fileName,
//             'directory' => $directory
//         ], 202);
//     }