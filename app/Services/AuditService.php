<?php

namespace App\Services;

use Exception;
use WebSocket\Client;
use Throwable;

class AuditService
{
    /**
     * Выполнить аудит модуля по имени и опциональной версии.
     *
     * @param string $name
     * @param string|null $version
     * @return mixed
     * @throws Throwable
     */
    public function audit(string $name, ?string $version = null): mixed
    {
        $wsUrl = config('audit.websocket_url', 'ws://127.0.0.1:3000/ws');

        // Создаём WebSocket-клиента
        $client = new Client($wsUrl, [
            'timeout' => 3600,
            'headers' => [
                'User-Agent' => 'Laravel-AuditService/1.0',
            ],
        ]);

        // Формируем JSON-RPC 2.0 запрос
        $requestId = uniqid('audit_', true);
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'scan',
            'params' => array_filter([
                'modName' => $name,
                'version' => $version,
            ]),
            'id' => $requestId,
        ];

        // Отправляем сообщение (исправлено: send, а не text)
        $client->send(json_encode($request));

        // Получаем ответ
        $responseText = $client->receive();
        $response = json_decode($responseText, true);

        // Закрываем соединение
        $client->close();

        // Обрабатываем ошибки JSON-RPC
        if (isset($response['error'])) {
            $errorMsg = $response['error']['message'] ?? 'Неизвестная ошибка JSON-RPC';
            throw new Exception("Ошибка при сканировании: {$errorMsg}");
        }

        // Проверяем соответствие ID запроса (опционально)
        if (isset($response['id']) && $response['id'] !== $requestId) {
            throw new Exception('Несоответствие ID ответа запросу');
        }

        return $response['result'] ?? null;
    }
}
