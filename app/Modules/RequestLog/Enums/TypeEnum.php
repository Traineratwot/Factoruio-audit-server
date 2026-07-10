<?php

namespace App\Modules\RequestLog\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TypeEnum: string implements HasColor, HasIcon, HasLabel
{
    case Input = 'input';
    case Output = 'output';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Input => 'Inbound',
            self::Output => 'Outbound',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Input => 'warning',
            self::Output => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Input => 'heroicon-m-arrow-left',
            self::Output => 'heroicon-m-arrow-right',
        };
    }
}
