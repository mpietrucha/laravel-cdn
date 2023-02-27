<?php

namespace Mpietrucha\Cdn\Disks;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mpietrucha\Cdn\Url;
use Mpietrucha\Cdn\Contracts\DiskInterface;
use Mpietrucha\Support\Concerns\HasFactory;

class Sync implements DiskInterface
{
    use HasFactory;

    protected static ?FilesystemAdapter $disk = null;

    public function disk(): ?FilesystemAdapter
    {
        if (self::$disk) {
            return self::$disk;
        }

        if (! config('cdn.sync.enabled')) {
            return null;
        }

        $configuration = collect_config('cdn.sync.disk');

        if ($configuration->get('driver') === 'local') {
            $configuration->put('root', storage_path($configuration->get('root')));
        }

        return self::$disk = Storage::build([
            ...$configuration,
            'url' => Url::withPath('cdn.sync.path'),
        ]);
    }
}
