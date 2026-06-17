<?php

namespace App\Models;

use App\Facades\AuditService;
use App\Http\Resources\ModResource;
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
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
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
            'latest_version' => $this->latest_version,
            'downloads_count' => $this->downloads_count,
            'popularity' => $this->popularity,
        ];
    }
}
