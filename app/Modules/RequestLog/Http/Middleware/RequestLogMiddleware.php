<?php

namespace App\Modules\RequestLog\Http\Middleware;

use App\Modules\RequestLog\Enums\TypeEnum;
use App\Modules\RequestLog\Models\RequestLog;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $log = null;
        try {
            $log = RequestLog::start(
                type: TypeEnum::Input,
                url: $request->url(),
                method: $request->method(),
                request_body: $request->getContent(),
                request_head: $request->headers->all(),
            );
        } catch (Exception $e) {
            Log::error($e);
        }

        $response = $next($request);

        try {
            $response_head = null;
            $response_body = $response;
            if ($response instanceof Response) {
                $response_head = $response->headers->all();
                $response_body = $response->getContent();
            }
            $log?->end(
                response_body: $response_body,
                response_head: $response_head,
                status_code: $response->status(),
            );
        } catch (Exception $e) {
            Log::error($e);
        }

        return $response;
    }
}
