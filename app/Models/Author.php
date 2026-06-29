<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Author extends Model
{
    use Searchable;

    protected $guarded = ['id'];

    public function mods(): HasMany
    {
        return $this->hasMany(Mod::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => str($this->name)->limit(256)->ascii(),
        ];
    }
}
