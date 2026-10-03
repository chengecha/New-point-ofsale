<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class folder_user_permissions extends Model
{
    //

    protected $fillable = [
        'folder_id',
        'user_id',
        'can_read',
        'can_upload',
        'can_delete',
    ];

    public function folder()
    {
        return $this->belongsTo(Directory::class, 'folderid');
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
