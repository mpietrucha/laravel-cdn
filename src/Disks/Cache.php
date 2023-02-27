<?php

namespace Mpietrucha\Cdn\Disks;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mpietrucha\Cdn\Contracts\DiskInterface;
use Mpietrucha\Support\Concerns\HasFactory;

class Cache implements DiskInterface
{
    use HasFactory;

    protected static ?FilesystemAdapter $disk = null;

    public function disk(): ?FilesystemAdapter
    {
        if (! config('cdn.cache.enabled')) {
            return null;
        }

        return self::$disk ??= Storage::build(config('cdn.cache.disk'));
    }
}
