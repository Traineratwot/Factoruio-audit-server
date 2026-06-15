<?php

namespace App\Console\Commands\Startup;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConnectDb extends Command
{
    protected $signature = 'startup:connect:db';

    protected $description = 'Проверка соединение с базой данных MySQL для работы в докере';

    public function handle(): void
    {
        $start = time();
        $end = $start + 120; // 2 минуты
        $this->question("Проверка соединение с базой данных All time: '.$end");
        $i = 0;
        while (time() < $end) {
            $i++;
            $this->warn('Попытка №' . $i . " \n");
            try {
                DB::connection()->getPdo();
                $this->info('Соединение с базой данных установлено.');
                exit(0);
            } catch (Exception $e) {
                $this->warn('Не удалось установить соединение с базой данных: ' . $e->getMessage() . ' timeout 5s');
            }

            sleep(5); // Пауза в 5 секунд перед следующей проверкой
        }
        $this->error('Не удалось установить соединение с базой данных.');
        exit(5);
    }
}
