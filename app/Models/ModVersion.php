<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModVersion extends Model
{
    protected $guarded = ['id'];

    public function getUrl(): ?string
    {
        if (!blank($this->download_url)) {
            return 'hhttps://mods.factorio.com' . $this->download_url;
        }
        return null;
    }

    protected function casts(): array
    {
        return [
            'dependencies' => 'array',
            'released_at' => 'datetime',
        ];
    }

    public function mod(): BelongsTo
    {
        return $this->belongsTo(Mod::class);
    }
}
