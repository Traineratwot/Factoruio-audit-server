<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    protected $guarded = ['id'];

    public function mods(): HasMany
    {
        return $this->hasMany(Mod::class);
    }
}
