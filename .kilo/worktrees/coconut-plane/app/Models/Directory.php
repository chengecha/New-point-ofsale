<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\folder_user_permissions;

class Directory extends Model
{
    //
    use HasFactory;

    protected $fillable = [

        'folderid',
        'foldername',
        'description',
        'created_by',
        'updated_by',
        'parent',
        'is_active',
    ];
public function permissions()
{
    return $this->hasMany(folder_user_permissions::class, 'folder_id');
}
}
