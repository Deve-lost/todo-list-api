<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    use HasFactory;

    protected $table    = 'items';
    protected $guarded  = [];

    // Relations
    public function checklists()
    {
        return $this->belongsTo(Checklists::class, 'id');
    }
}
