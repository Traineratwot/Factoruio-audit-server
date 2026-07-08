<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
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

        $result = $this->request('scan', array_filter([
            'modName' => $name,
            'version' => $version,
        ]));

        Log::info('Аудит успешно завершен', ['result' => $result]);

        return $result;
    }

    /**
     * Получить версию сканнера.
     *
     * @throws Throwable
     */
    public function scannerVersion(): string
    {
        Log::info('Запрос версии сканера');

        $result = $this->request('scanner_version');

        Log::info('Версия сканера получена', ['version' => $result]);

        return $result['version'];
    }

    /**
     * Получить версию сканнера из кеша (1 час), или запросить если кеш пуст.
     */
    public function cachedScannerVersion(): ?string
    {
        try {
            return Cache::remember('scanner.version', 3600, fn () => $this->scannerVersion());
        } catch (Throwable $e) {
            Log::warning('Не удалось получить версию сканера', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Отправить JSON-RPC 2.0 запрос к WebSocket серверу.
     *
     * @param  array<string, mixed>  $params
     *
     * @throws Throwable
     */
    private function request(string $method, array $params = []): mixed
    {
        try {
            $wsUrl = config('audit.websocket_url', 'ws://127.0.0.1:3000/ws');
            Log::debug('Подключение к WebSocket', ['url' => $wsUrl]);

            $client = new Client($wsUrl, [
                'timeout' => 3600,
                'headers' => [
                    'User-Agent' => 'Laravel-AuditService/1.0',
                ],
            ]);

            $requestId = uniqid('rpc_', true);
            $request = [
                'jsonrpc' => '2.0',
                'method' => $method,
                'params' => $params,
                'id' => $requestId,
            ];

            Log::debug('Отправка JSON-RPC запроса', ['request' => $request]);

            $client->send(json_encode($request));

            $responseText = $client->receive();
            Log::debug('Получен сырой ответ', ['responseText' => $responseText]);

            $response = json_decode($responseText, true);
            Log::debug('Декодированный ответ', ['response' => $response]);

            $client->close();

            if (isset($response['error'])) {
                $errorMsg = $response['error']['message'] ?? 'Неизвестная ошибка JSON-RPC';
                Log::error('Ошибка JSON-RPC', ['error' => $response['error']]);
                throw new Exception("Ошибка JSON-RPC ({$method}): {$errorMsg}");
            }

            if (isset($response['id']) && $response['id'] !== $requestId) {
                Log::error('Несоответствие ID ответа запросу', [
                    'expected' => $requestId,
                    'actual' => $response['id'] ?? null,
                ]);
                throw new Exception('Несоответствие ID ответа запросу');
            }

            return $response['result'] ?? null;
        } catch (Throwable $e) {
            Log::error('Исключение при выполнении JSON-RPC запроса', [
                'method' => $method,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
