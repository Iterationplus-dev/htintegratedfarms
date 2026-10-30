<?php

namespace App\Support;

use InvalidArgumentException;

/** Inline SVG loader for Phosphor Icons (MIT), regular weight, stored in resources/svg/phosphor. */
final class Icon
{
    /** @var array<string, string> */
    private static array $cache = [];

    public static function svg(string $name): string
    {
        return self::$cache[$name] ??= self::load($name);
    }

    private static function load(string $name): string
    {
        $path = resource_path("svg/phosphor/{$name}.svg");

        if (! preg_match('/^[a-z0-9-]+$/', $name) || ! is_file($path)) {
            throw new InvalidArgumentException("Unknown icon [{$name}].");
        }

        return (string) preg_replace('/^<svg\b/', '<svg aria-hidden="true" focusable="false"', trim((string) file_get_contents($path)));
    }
}
