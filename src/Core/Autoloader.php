<?php

namespace TribuApi\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoload PSR-4 du namespace TribuApi\ vers src/, sans dépendance à Composer.
 */
final class Autoloader
{
    private const string PREFIX = 'TribuApi\\';

    public static function register(): void
    {
        spl_autoload_register(self::load(...));
    }

    private static function load(string $class): void
    {
        if (!str_starts_with($class, self::PREFIX)) {
            return;
        }

        $relative = str_replace('\\', '/', substr($class, strlen(self::PREFIX)));
        $file = dirname(__DIR__) . '/' . $relative . '.php';

        if (is_file($file)) {
            require_once $file;
        }
    }
}
