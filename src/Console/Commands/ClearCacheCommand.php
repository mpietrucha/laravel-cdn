<?php

namespace Mpietrucha\Cdn\Console\Commands;

use Illuminate\Console\Command;
use Mpietrucha\Cdn\Disks\Cache;
use Mpietrucha\Cdn\Disks\Sync;

class ClearCacheCommand extends Command
{
    protected $signature = 'cdn:clear';

    public function handle(): void
    {
        $this->components->task('Clearing cache folder.', function () {
            Cache::create()->disk()?->deleteDirectory(DIRECTORY_SEPARATOR);
        });

        $this->components->task('Clearing sync folder', function () {
            Sync::create()->disk()?->deleteDirectory(DIRECTORY_SEPARATOR);
        });
    }
}
