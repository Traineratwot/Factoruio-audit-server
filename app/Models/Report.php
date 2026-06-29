<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Report extends Model
{
    use Searchable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'raw' => 'array',
        ];
    }

    public function mod(): BelongsTo
    {
        return $this->belongsTo(Mod::class);
    }

    public function getUrl(): string
    {
        return route('report.mod.version', [
            'mod' => $this->mod->name,
            'version' => $this->mod_version,
        ]);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'mod_name' => str($this->mod?->name)->limit(256)->ascii(),
            'mod_version' => $this->mod_version,
            'sha1' => $this->sha1,
            'score' => $this->score,
        ];
    }
}
