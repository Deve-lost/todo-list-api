<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklists extends Model
{
    use HasFactory;

    protected $table    = 'checklists';
    protected $guarded  = [];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
