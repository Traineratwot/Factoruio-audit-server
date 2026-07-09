<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class ModVersion extends Model
{
    protected $guarded = ['id'];

    public function getUrl(): ?string
    {
        if (! blank($this->download_url)) {
            return 'https://mods.factorio.com'.$this->download_url;
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

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'mod_id' => $this->mod_id,
            'mod_name' => str($this->mod?->name)->limit(256)->ascii(),
            'version' => $this->version,
            'file_name' => str($this->file_name)->limit(256)->ascii(),
            'factorio_version' => $this->factorio_version,
            'released_at' => $this->released_at?->timestamp,
        ];
    }
}
