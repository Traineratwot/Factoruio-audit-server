<?php

namespace App\Services;

use Exception;
use Tivoka\Client;

class AuditService
{
    /**
     * Получить WebSocket-клиента, используя URL из конфига.
     *
     * @return Client\Connection\ConnectionInterface
     */
    public function client()
    {
        // Получаем URL подключения из конфигурационного файла
        // Пример конфига: config/audit.php с ключом 'websocket_url'
        $wsUrl = config('audit.websocket_url', 'ws://127.0.0.1:3000/ws');
        return Client::connect($wsUrl);
    }

    /**
     * Выполнить аудит модуля по имени и опциональной версии.
     *
     * @param string $name
     * @param string|null $version
     * @return mixed
     * @throws \Throwable
     */
    public function audit(string $name, ?string $version = null)
    {
        // Получаем соединение через метод client()
        $connection = $this->client();
        $request = [
            'modName' => $name,

        ];
        if ($version) {
            $request['version'] = $version;
        }
        $response = $connection->sendRequest('scan', $request);
        throw_if($response->error, new Exception("Ошибка при сканировании: " . $response->errorMessage));
        return $response->result;
    }
}
