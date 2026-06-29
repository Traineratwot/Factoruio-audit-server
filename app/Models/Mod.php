<?php

namespace App\Models;

use App\Facades\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Throwable;

class Mod extends Model
{
    use Searchable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'downloads_count' => 'integer',
            'popularity' => 'float',
            'license' => 'array',
            'tags' => 'array',
            'images' => 'array',
            'releases' => 'array',
            'score' => 'float',
            'latest_release_date' => 'datetime',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ModVersion::class)->orderByDesc('released_at');
    }

    /**
     * Get the latest version number from releases JSON or attribute.
     */
    public function getLatestVersionAttribute(): ?string
    {
        return $this->attributes['latest_version']
            ?? $this->releases[0]['version'] ?? null;
    }

    /**
     * Get the first release's factorio_version.
     */
    public function getFactorioVersionAttribute(): ?string
    {
        return $this->attributes['factorio_version']
            ?? $this->releases[0]['info_json']['factorio_version'] ?? null;
    }

    public function getLatestReleaseDateAttribute(): ?string
    {
        return $this->attributes['latest_release_date']
            ?? $this->releases[0]['released_at'] ?? null;
    }

    /**
     * @throws Throwable
     */
    public function runAudit(?string $version = null): ?Report
    {
        $data = AuditService::audit($this->name, $this->latest_report);
        $report = Report::updateOrCreate(
            [
                'mod_id' => Mod::where('name', $data['report']['modName'])->firstOrFail()?->id,
                'mod_version' => $version ?? $data['report']['version'] ?? null,
                'sha1' => $data['report']['sha1'],
            ],
            [
                'raw' => $data,
                'score' => $data['report']['score'],
                'scannerVersion' => $data['report']['scannerVersion'],
            ]
        );

        return $report instanceof Report ? $report : null;
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => str($this->name)->limit(256)->ascii(),
            'title' => str($this->title)->limit(256)->ascii(),
            'summary' => str($this->summary)->limit(256)->ascii(),
            'owner' => str($this->owner)->limit(256)->ascii(),
            'category' => str($this->category)->limit(256)->ascii(),
            'tags' => $this->tags ?? [],
            'latest_version' => $this->latest_version,
            'downloads_count' => $this->downloads_count,
            'popularity' => $this->popularity,
        ];
    }
}
