<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name'
    ];

    public $timestamps = false;

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
