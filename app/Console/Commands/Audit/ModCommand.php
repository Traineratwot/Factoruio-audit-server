<?php

namespace App\Console\Commands\Audit;

use App\Models\Report;
use Illuminate\Console\Command;
use Tivoka\Client;

class ModCommand extends Command
{
    protected $signature = 'audit:mod';

    protected $description = 'test';

    public function handle()
    {
        $connection = Client::connect('ws://127.0.0.1:3000/ws');
        $request = $connection->sendRequest('scan', [
            "modName" => 'flib',
//            "version" => '0.1.0'
        ]);
        Report::updateOrCreate(
            [
                'mod_name' => $request->result['report']['modName'],
                'mod_version' => $request->result['report']['version'],
                'sha1' => $request->result['report']['sha1'],
            ],
            [
                'raw' => $request->result,
                'score' => $request->result['report']['score'],
                'scannerVersion' => $request->result['report']['scannerVersion'],
            ]
        );
        dd($request->result);
    }
}
