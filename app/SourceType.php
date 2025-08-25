<?php

namespace App;

enum SourceType: string
{
    case LOCAL = 'local';
    case EXTERNAL = 'external';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
