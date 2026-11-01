<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frontpage extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['layout' => 'array'];
    }
}
