<?php

namespace App\Http\Controllers;

use App\Http\Resources\FolderPermissionResource;
use App\Http\Resources\UserPermissionResource;
use App\Models\Directory;
use App\Models\folder_user_permissions;
use App\Models\FolderUserPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class folderUserPermissionController extends Controller
{
    // List permissions for a folder
    public function index(Directory $directory)
    {
        $permission = $directory->permissions()->with('user')->get();


        return FolderPermissionResource::collection($permission);
    }

    // Assign or update permission
    public function store(Request $request, Directory $directory)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'can_read' => 'boolean',
            'can_upload' => 'boolean',
            'can_delete' => 'boolean',
        ]);

          folder_user_permissions::create([
              'folder_id' => $directory->id,
              'user_id' => $validated['user_id'],
              'can_read' => $validated['can_read'] ?? true,
              'can_upload' => $validated['can_upload'] ?? false,
              'can_delete' => $validated['can_delete'] ?? false
            ]);

        return response()->json(["msg" => "New User Folder permission added successfully"], 201);
    }

    // Show permission for a folder/user
    public function show(Directory $directory, User $user)
    {
        $permission = folder_user_permissions::where('folder_id', $directory->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        //  return $permission;

        return new UserPermissionResource($permission);
    }

    // Update permission
    public function update(Request $request, Directory $directory, User $user)
    {
        $permission = folder_user_permissions::where('folder_id', $directory->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'can_read' => 'boolean',
            'can_upload' => 'boolean',
            'can_delete' => 'boolean',
        ]);

        $permission->update($validated);

        return response()->json(['message' => 'Permission updated'],201);
    }

    // Remove permission
    public function destroy(Directory $directory, User $user)
    {
        folder_user_permissions::where('folder_id', $directory->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Permission removed']);
    }
}

