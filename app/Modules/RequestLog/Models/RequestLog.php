<?php

namespace App\Modules\RequestLog\Models;

use App\Modules\RequestLog\Enums\MethodsEnum;
use App\Modules\RequestLog\Enums\TypeEnum;
use Exception;
use GuzzleHttp\TransferStats;
use GuzzleLogMiddleware\Handler\HandlerInterface;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Stringable;
use Throwable;

class RequestLog extends Model implements HandlerInterface
{
    use Prunable;

    protected static ?EloquentModel $subjectContext = null;

    protected $guarded = ['id'];

    private Carbon $timeStart;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->timeStart = new Carbon;
    }

    public static function forSubject(?EloquentModel $model, callable $callback)
    {
        $prev = static::$subjectContext;
        static::$subjectContext = $model;
        try {
            if (class_exists(Context::class)) {
                Context::add('requestlog.subject', $model);
            }

            return $callback();
        } finally {
            static::$subjectContext = $prev;
            if (class_exists(Context::class)) {
                Context::forget('requestlog.subject');
            }
        }
    }

    public static function setSubjectContext(?EloquentModel $model): void
    {
        static::$subjectContext = $model;
        if (class_exists(Context::class)) {
            Context::add('requestlog.subject', $model);
        }
    }

    public static function start(
        TypeEnum $type,
        string $url,
        string $method,
        string|array|object|null $request_body = null,
        ?array $request_head = null,
    ): self {
        $method = Str::upper($method);
        $log = new self;
        $log->type = $type;
        $log->url = $url;
        $log->method = $method;
        $log->setRequestBody($request_body);
        $log->request_head = $request_head;
        $log->save();

        return $log;
    }

    public function setRequestBody(string|array|object|null $body): self
    {
        if ($body instanceof StreamInterface) {
            $body->rewind();
            $body = $body->getContents();
        } elseif ($body instanceof Stringable) {
            $body = (string) $body;
        } elseif (is_array($body) || is_object($body)) {
            $body = json_encode($body) ?: null;
        }
        $len = Str::length($body ?? '');
        if ($len > 0 && $len < config('requestlog.size_limit')) {
            $this->request_body = $body;
        } elseif ($len > 0) {
            $this->request_body = 'Log not recorded: data exceeds limit of '.Number::fileSize(config('requestlog.size_limit'));
        } else {
            $this->request_body = null;
        }

        return $this;
    }

    public function prunable()
    {
        return $this->where('created_at', '<', now()->subDays(30));
    }

    public function end(
        string|array|object|null $response_body = null,
        ?array $response_head = null,
        ?int $status_code = null,
        ?EloquentModel $subject = null,
    ): self {
        $contentType = Arr::get($response_head, 'content-type.0');
        if (! $this->isBinaryContent($contentType)) {
            $this->setResponseBody($response_body);
        } else {
            $this->setResponseBody('Log not recorded: response is binary ('.$contentType.')');
        }
        $this->response_head = $response_head;
        $this->status_code = $status_code;
        $time = $this->timeStart->diffInSeconds();
        if ($time) {
            $this->time = $time;
        }
        if ($this->status_code) {
            $this->completed = true;
        }
        $subjectFromContext = class_exists(Context::class) ? Context::get('requestlog.subject') : null;
        $this->setSubject($subject ?? $subjectFromContext ?? static::$subjectContext);

        $this->save();

        return $this;
    }

    private function isBinaryContent(?string $contentType): bool
    {
        return preg_match('/^application\/octet-stream|image\/.*|audio\/.*$/', strtolower($contentType)) === 1;
    }

    public function setResponseBody(string|array|object|null $body): self
    {
        if ($body instanceof StreamInterface) {
            $body->rewind();
            $body = $body->getContents();
        } elseif ($body instanceof Stringable) {
            $body = (string) $body;
        } elseif (is_array($body) || is_object($body)) {
            $body = json_encode($body) ?: null;
        }
        $len = Str::length($body ?? '');
        if ($len > 0 && $len < config('requestlog.size_limit')) {
            $this->response_body = $body;
        } elseif ($len > 0) {
            $this->response_body = 'Log not recorded: data exceeds limit of '.Number::fileSize(config('requestlog.size_limit'));
        } else {
            $this->response_body = null;
        }

        return $this;
    }

    public function setSubject(?EloquentModel $model): self
    {
        if ($model) {
            $this->subject()->associate($model);
        }

        return $this;
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getRequestBody(): string|array|null
    {
        if (! is_array($this->request_body) && json_validate($this->request_body ?: '')) {
            return Json::decode($this->request_body, true);
        }

        return $this->request_body;
    }

    public function getRequestQuery(): string|array|null
    {
        if (! is_array($this->request_query) && json_validate($this->request_query ?: '')) {
            return Json::decode($this->request_query, true);
        }

        return $this->request_query;
    }

    public function getResponseBody(): string|array|null
    {
        if (! is_array($this->response_body) && json_validate($this->response_body ?: '')) {
            return Json::decode($this->response_body, true);
        }

        return $this->response_body;
    }

    public function log(
        LoggerInterface $logger,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?Throwable $exception = null,
        ?TransferStats $stats = null,
        array $options = []
    ): void {
        $this->type = TypeEnum::Output;
        try {
            $this->method = $request->getMethod();
            $this->setUrl($request->getUri());
            $this->setRequestBody($request->getBody());
            $this->request_head = $request->getHeaders();
            $this->setResponseBody($response?->getBody());
            $this->response_head = $response?->getHeaders();
            $this->status_code = $response?->getStatusCode();

            $subject = data_get($options, 'request_log.subject');

            if (! $subject && class_exists(Context::class)) {
                $subject = Context::get('requestlog.subject');
            }

            if (! $subject) {
                $subject = static::$subjectContext;
            }

            if ($subject instanceof EloquentModel) {
                $this->setSubject($subject);
            }

            $time = $this->timeStart->diffInSeconds();
            if ($time) {
                $this->time = $time;
            }
            if ($this->status_code) {
                $this->completed = true;
            }
        } catch (Exception $e) {
            $this->response_body .= $e->getMessage();
        } finally {
            $this->save();
        }
    }

    public function setUrl(mixed $url): self
    {
        $url = (string) $url;
        $parts = explode('?', $url);
        $this->url = $parts[0];
        if (isset($parts[1])) {
            parse_str($parts[1], $query);
            $this->request_query = $query;
        }

        return $this;
    }

    protected function casts(): array
    {
        return [
            'type' => TypeEnum::class,
            'method' => MethodsEnum::class,
            'request_head' => 'array',
            'response_head' => 'array',
            'completed' => 'boolean',
            'time' => 'float',
            'request_query' => 'array',
        ];
    }
}
