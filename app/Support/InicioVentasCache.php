<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class InicioVentasCache
{
    private const VERSION_KEY = 'inicio_resumen_ventas:version';

    public static function version(): int
    {
        return max(1, (int) Cache::get(self::VERSION_KEY, 1));
    }

    public static function bust(): int
    {
        $nextVersion = self::version() + 1;

        Cache::forever(self::VERSION_KEY, $nextVersion);

        return $nextVersion;
    }
}
