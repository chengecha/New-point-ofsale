<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoredFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'path',
        'size',
        'mime_type',
        'folder',
        'status',
        'uploaded_at'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}