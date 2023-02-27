<?php

namespace Mpietrucha\Cdn\Contracts;

use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response;

interface DriverInterface
{
    public function __construct(CurrentFileResolverInterface $file);

    public static function handles(): array;

    public function stream(): StreamInterface;

    public function response(): Response;
}
