<?php

namespace Mpietrucha\Cdn\Contracts;

use Illuminate\Filesystem\FilesystemAdapter;

interface DiskInterface
{
    public function disk(): ?FilesystemAdapter;
}
