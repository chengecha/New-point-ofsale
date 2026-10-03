<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderPermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'folder_id'   => $this->folder_id,
            'user_id'     => $this->user_id,
            'can_read'    => (bool) $this->can_read,
            'can_upload'  => (bool) $this->can_upload,
            'can_delete'  => (bool) $this->can_delete,
            //'created_at'  => $this->created_at,
            //'updated_at'  => $this->updated_at,

            // Nested user
            'user' => [
                'id'                => $this->user?->id,
                'name'              => $this->user?->name,
                'email'             => $this->user?->email,
                //'email_verified_at' => $this->user?->email_verified_at,
                //'created_at'        => $this->user?->created_at,
                //'updated_at'        => $this->user?->updated_at,
            ],
        ];
    }
}
