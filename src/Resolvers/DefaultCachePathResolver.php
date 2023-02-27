<?php

namespace Mpietrucha\Cdn\Resolvers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Mpietrucha\Cdn\Contracts\CachePathResolverInterface;
use Mpietrucha\Cdn\Contracts\CurrentFileResolverInterface;

class DefaultCachePathResolver implements CachePathResolverInterface
{
    protected ?string $path = null;

    public function __construct(protected CurrentFileResolverInterface $file)
    {
    }

    public function path(): string
    {
        return $this->path ??= $this->generatePath();
    }

    protected function generatePath(): string
    {
        $cache = str($this->file->url())->md5()->toLettersCollection();

        return Collection::times(config('cdn.cache.levels'))->map(fn () => $cache->pop())
            ->push($cache->toWord())
            ->toDirectory()
            ->extension(File::extension($this->file->url()));
    }
}
