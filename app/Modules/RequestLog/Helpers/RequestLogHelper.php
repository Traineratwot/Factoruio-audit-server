<?php

namespace App\Modules\RequestLog\Helpers;

use App\Modules\RequestLog\Models\RequestLog;
use GuzzleHttp\HandlerStack;
use GuzzleLogMiddleware\LogMiddleware;
use Monolog\Logger;

class RequestLogHelper
{
    public static function createHandlerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(new LogMiddleware(new Logger('rest'), new RequestLog));

        return $stack;
    }
}
