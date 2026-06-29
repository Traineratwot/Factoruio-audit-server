<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;
use WebSocket\Client;

class AuditService
{
    /**
     * Выполнить аудит модуля по имени и опциональной версии.
     *
     * @throws Throwable
     */
    public function audit(string $name, ?string $version = null): mixed
    {
        Log::info('Запуск аудита', ['name' => $name, 'version' => $version]);

        try {
            $wsUrl = config('audit.websocket_url', 'ws://127.0.0.1:3000/ws');
            Log::debug('Подключение к WebSocket', ['url' => $wsUrl]);

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

            Log::debug('Отправка JSON-RPC запроса', ['request' => $request]);

            // Отправляем сообщение
            $client->send(json_encode($request));

            // Получаем ответ
            $responseText = $client->receive();
            Log::debug('Получен сырой ответ', ['responseText' => $responseText]);

            $response = json_decode($responseText, true);
            Log::debug('Декодированный ответ', ['response' => $response]);

            // Закрываем соединение
            $client->close();
            Log::debug('Соединение закрыто');

            // Обрабатываем ошибки JSON-RPC
            if (isset($response['error'])) {
                $errorMsg = $response['error']['message'] ?? 'Неизвестная ошибка JSON-RPC';
                Log::error('Ошибка JSON-RPC', ['error' => $response['error']]);
                throw new Exception("Ошибка при сканировании: {$errorMsg}");
            }

            // Проверяем соответствие ID запроса (опционально)
            if (isset($response['id']) && $response['id'] !== $requestId) {
                Log::error('Несоответствие ID ответа запросу', [
                    'expected' => $requestId,
                    'actual' => $response['id'] ?? null,
                ]);
                throw new Exception('Несоответствие ID ответа запросу');
            }

            $result = $response['result'] ?? null;
            Log::info('Аудит успешно завершен', ['result' => $result]);

            return $result;
        } catch (Throwable $e) {
            Log::error('Исключение при выполнении аудита', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
