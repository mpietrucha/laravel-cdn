<?php

namespace Mpietrucha\Cdn\Http\Controllers;

use Mpietrucha\Cdn\Contracts\CachePathResolverInterface;
use Mpietrucha\Cdn\Contracts\CurrentFileResolverInterface;
use Mpietrucha\Cdn\Disks\Cache;
use Mpietrucha\Cdn\Manipulator;
use Symfony\Component\HttpFoundation\Response;

class CdnController
{
    public function __invoke(Manipulator $manipulator, ?string $path = null): Response
    {
        if (! $path) {
            abort(404);
        }

        $file = app(CurrentFileResolverInterface::class, ['path' => $path]);

        $disk = Cache::create()->disk();

        if (! $disk && $file->exists()) {
            return $manipulator->load($file)->response() ?? abort(404);
        }

        if (! $file->exists()) {
            abort(404);
        }

        $cache = app(CachePathResolverInterface::class, ['file' => $file]);

        if ($disk->exists($cache->path())) {
            return $disk->response($cache->path());
        }

        return $manipulator->load($file)->saveTo($disk, $cache)->response() ?? abort(404);
    }
}
