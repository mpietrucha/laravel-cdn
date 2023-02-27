<?php

namespace Mpietrucha\Cdn;

use Illuminate\Filesystem\FilesystemAdapter;
use Mpietrucha\Cdn\Contracts\CachePathResolverInterface;
use Mpietrucha\Cdn\Contracts\CurrentFileResolverInterface;
use Mpietrucha\Cdn\Contracts\DriverInterface;
use Mpietrucha\Cdn\Resolvers\DriverResolver;
use Symfony\Component\HttpFoundation\Response;

class Manipulator
{
    protected ?DriverInterface $driver;

    protected ?FilesystemAdapter $disk = null;

    protected ?CachePathResolverInterface $cache = null;

    public function load(CurrentFileResolverInterface $file): self
    {
        $this->driver = DriverResolver::resolve($file);

        return $this;
    }

    public function saveTo(FilesystemAdapter $disk, CachePathResolverInterface $cache): self
    {
        [$this->disk, $this->cache] = [$disk, $cache];

        return $this;
    }

    public function response(): ?Response
    {
        if (! $this->driver) {
            return null;
        }

        if ($this->disk) {
            $this->disk->writeStream($this->cache->path(), $this->driver->stream()->detach());
        }

        return $this->driver->response();
    }
}
