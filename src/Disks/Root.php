<?php

namespace Mpietrucha\Cdn\Disks;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mpietrucha\Cdn\Url;
use Mpietrucha\Cdn\Contracts\DiskInterface;
use Mpietrucha\Support\Concerns\HasFactory;

class Root implements DiskInterface
{
    use HasFactory;

    protected static ?FilesystemAdapter $disk = null;

    public function disk(): ?FilesystemAdapter
    {
        if (! config('cdn.public')) {
            return null;
        }

        return self::$disk ??= Storage::build([
            'driver' => 'local',
            'root' => public_path(),
            'url' => Url::toString()
        ]);
    }
}
