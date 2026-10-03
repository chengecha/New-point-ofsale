<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
    return [
        'folderid'=>$this->id,
        'foldername' => $this-> foldername,
        'description' => $this-> description,
        'parent'=> $this-> parent,
         'status' => $this->status ?  'Inactive': 'Active'
       ];
    }
}
