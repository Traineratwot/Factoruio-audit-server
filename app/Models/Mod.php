<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tivoka\Client;

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

    public function runAudit()
    {
        $connection = Client::connect('ws://127.0.0.1:3000/ws');
        $request = $connection->sendRequest('scan', [
            'modName' => $this->name,
            "version" => $this->latest_version
        ]);
        Report::updateOrCreate(
            [
                'mod_id' => Mod::where('name', $request->result['report']['modName'])->firstOrFail()->id,
                'mod_version' => $request->result['report']['version'],
                'sha1' => $request->result['report']['sha1'],
            ],
            [
                'raw' => $request->result,
                'score' => $request->result['report']['score'],
                'scannerVersion' => $request->result['report']['scannerVersion'],
            ]
        );
    }
}
