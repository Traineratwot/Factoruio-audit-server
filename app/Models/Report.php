<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
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
}
