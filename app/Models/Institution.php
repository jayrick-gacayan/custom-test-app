<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name'];

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }
}
