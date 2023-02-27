<?php

namespace Mpietrucha\Cdn\Resolvers;

use Illuminate\Support\Collection;
use Mpietrucha\Cdn\Contracts\CurrentFileResolverInterface;
use Mpietrucha\Cdn\Contracts\DriverInterface;
use Mpietrucha\Cdn\Drivers;
use Mpietrucha\Support\Condition;

class DriverResolver
{
    protected const DRIVERS = [
        Drivers\ImageDriver::class,
        Drivers\TextDriver::class,
    ];

    protected static ?Collection $instances = null;

    public static function resolve(CurrentFileResolverInterface $file): ?DriverInterface
    {
        self::$instances ??= collect(self::DRIVERS);

        $driver = self::$instances->first(fn (string $driver) => self::handles($driver, $file));

        return with($driver, fn (?string $driver) => Condition::create()
            ->add(fn () => new $driver($file), $driver)
            ->resolve());
    }

    protected static function handles(string $driver, CurrentFileResolverInterface $file): bool
    {
        return collect($driver::handles())->first(fn (string $mime) => $file->isMimeType($mime)) !== null;
    }
}
