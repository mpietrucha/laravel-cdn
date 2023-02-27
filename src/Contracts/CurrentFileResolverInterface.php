<?php

namespace Mpietrucha\Cdn\Contracts;

use Illuminate\Support\Collection;

interface CurrentFileResolverInterface
{
    public function __construct(string $path);

    public function exists(): bool;

    public function contents(): ?string;

    public function mimeType(): ?string;

    public function isMimeType(string $mime): bool;

    public function arguments(array $options = [], array $globals = []): Collection;

    public function url(array $options = [], array $globals = []): ?string;
}
