<?php

namespace Mpietrucha\Cdn\Components;

use Closure;
use Mpietrucha\Cdn\Disks\Sync;
use Mpietrucha\Cdn\Url;

class Storage
{
    public function cdn(): Closure
    {
        return function (string $path, array $options = []): string {
            $url = $this->url($file);

            if ($this->missing($path)) {
                return $url;
            }

            if (! $disk = Sync::create()->disk()) {
                return $url;
            }

            if (Url::hostsMatches($url)) {
                return cdn($url, $options);
            }

            $disk->when(! $disk->exists($path), fn (FilesystemAdapter $storage) => $storage->writeStream($path, $this->readStream($file)));

            return cdn($disk->url($path), $options);
        };
    }
}
