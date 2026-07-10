<?php

namespace App\Modules\RequestLog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MethodsEnum: string implements HasColor, HasLabel
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case PATCH = 'PATCH';
    case OPTIONS = 'OPTIONS';
    case HEAD = 'HEAD';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::GET => 'primary',
            self::POST => 'success',
            self::PUT => 'warning',
            self::DELETE => 'danger',
            self::PATCH => 'info',
            self::OPTIONS, self::HEAD => 'secondary',
        };
    }
}
