<?php

namespace App\Models;

use App\Facades\AuditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Throwable;

class Mod extends Model
{
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
    public function runAudit(): ?Report
    {
        $data = AuditService::audit($this->name, $this->latest_report);
        $report = Report::updateOrCreate(
            [
                'mod_id' => Mod::where('name', $data['report']['modName'])->firstOrFail()?->id,
                'mod_version' => $data['report']['version'],
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
}
