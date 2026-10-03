<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray(Request $request): array
    {
        return [
            // 'id'          => $this->id,
            'folder_id'   => $this->folder_id,
            'user_id'     => $this->user_id,
            'can_read'    => (bool) $this->can_read,
            'can_upload'  => (bool) $this->can_upload,
            'can_delete'  => (bool) $this->can_delete
        ];

    }
}
