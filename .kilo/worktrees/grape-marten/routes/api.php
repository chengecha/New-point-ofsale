<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\folderUserPermissionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});

// Protected routes
 Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // File operations
    Route::post('/upload', [FileController::class, 'upload']);
    Route::post('/files', [FileController::class, 'listFiles']);
    Route::get('/files/uploaded', [FileController::class, 'listUploadedFiles']);
    Route::get('/download/{filename}', [FileController::class, 'download']);

    Route::post('/download-url/{filename}', [FileController::class, 'getDownloadUrl']);

    Route::delete('/files/{filename}', [FileController::class, 'deleteFile']);

    Route::apiResource('directories', DirectoryController::class);



    // Route::get('directories/{directory}/permissions', [folderUserPermissionController::class, 'index']);
    // Route::post('directories/{directory}/permissions',[folderUserPermissionController::class, 'store']);
    // Route::get('directories/{directory}/permissions/{user}', [folderUserPermissionController::class, 'show']);
    // Route::put('directories/{directory}/permissions/{user}', [folderUserPermissionController::class, 'update']);
    // Route::delete('directories/{directory}/permissions/{user}',[folderUserPermissionController::class, 'destroy']);

    Route::get('GetFolderPermissions/{directory}', [folderUserPermissionController::class, 'index']);

    Route::post('AssignFolderPermissions/{directory}',[folderUserPermissionController::class, 'store']);

    Route::get('ShowPermissionforFolderorUser/{directory}/{user}', [folderUserPermissionController::class, 'show']);

    Route::put('UpdateFolderPermission/{directory}/{user}', [folderUserPermissionController::class, 'update']);

    Route::delete('DeleteFolderPermission/{directory}/{user}',[folderUserPermissionController::class, 'destroy']);

});