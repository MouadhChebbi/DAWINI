<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'files';
    protected $primaryKey = 'file_id';
    protected $fillable = [
        'file_name',
        'file_type',
        'file_size',
        'created_at',
        'user_id'

    ];
     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
