<?php

namespace Mpietrucha\Cdn;

use Exception;
use Spatie\Url\Url as Factory;

class Url
{
    protected static ?Factory $url = null;

    public static function __callStatic(string $method, array $arguments): string|Factory
    {
        self::init();

        return self::$url->$method(...$arguments);
    }

    public static function hostsMatches(string $url): bool
    {
        return self::getHost() === Factory::fromString($url)->getHost();
    }

    public static function pathFrom(string $url): Factory
    {
        return self::withPath(Factory::fromString($url)->getPath());
    }

    public static function toString(): string
    {
        self::init();

        return (string) self::$url;
    }

    protected static function init(): void
    {
        if (self::$url) {
            return;
        }

        if (! $subdomain = config('cdn.subdomain')) {
            throw new Exception('Invalid cdn subdomain given');
        }

        $url = Factory::fromString(config('app.url'));

        self::$url = $url->withHost($subdomain.'.'.$url->getHost());
    }
}
