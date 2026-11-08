<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = ['section', 'locale', 'value'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }
}
