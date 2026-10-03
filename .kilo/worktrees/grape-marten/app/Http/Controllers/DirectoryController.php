<?php

namespace App\Http\Controllers;

use App\Http\Resources\FolderResource;
use App\Models\Directory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DirectoryController extends Controller
{
    // List all directories
    public function index()
    {
        
       $folder = Directory::with('permissions')->get();

        return FolderResource::collection($folder);
    }

    // Create a directory
    public function store(Request $request)
    {
        $validated = $request->validate([
            'foldername' => 'required|string|alpha_dash|unique:directories,foldername',
            'description' => 'nullable|string',
            'parent' => 'nullable|string',
        ]);

       Directory::create([
            'foldername' => $validated['foldername'],
            'description' => $validated['description'],
            //'parent' => $validated['parent'],
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        return response()->json([
            "message" => "Directory created successfully",
        ], 201);
    }

    // Show a single directory
    public function show(Directory $directory)
    {
        return $directory->load('permissions');
    }

    // Update a directory
    public function update(Request $request, Directory $directory)
    {
        $validated = $request->validate([
            'foldername' => ['sometimes','string','alpha_dash',
             Rule::unique('directories', 'foldername')->ignore($directory->id),],
            'description' => 'nullable|string',
            'parent' => 'nullable','string',
            'is_active' => 'boolean'
            ]);

        $directory->update($validated + [
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            "message" => "Directory details updated successfully",
        ], 201);
    }

    // Delete a directory
    public function destroy(Directory $directory)
    {
        $directory->delete();

        return response()->json(['message' => 'Directory deleted']);
    }
}
