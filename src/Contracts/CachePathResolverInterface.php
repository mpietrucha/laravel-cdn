<?php

namespace Mpietrucha\Cdn\Contracts;

interface CachePathResolverInterface
{
    public function __construct(CurrentFileResolverInterface $file);

    public function path(): string;
}
