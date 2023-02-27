<?php

namespace Mpietrucha\Cdn\Resolvers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Stringable;
use Mpietrucha\Cdn\Contracts\CurrentFileResolverInterface;
use Mpietrucha\Cdn\Disks\Sync;
use Mpietrucha\Cdn\Disks\Root;
use Mpietrucha\Cdn\Url;
use Mpietrucha\Support\Base64;
use Mpietrucha\Support\Json;
use Mpietrucha\Support\Types;
use Mpietrucha\Support\Condition;
use Mpietrucha\Cdn\Contracts\DiskInterface;

class DefaultCurrentFileResolver implements CurrentFileResolverInterface
{
    protected string $file;

    protected ?string $source = null;

    protected ?FilesystemAdapter $disk = null;

    public function __construct(protected string $path)
    {
        $this->file = File::name($path);

        $this->findOnEnabledDisks();
    }

    public function exists(): bool
    {
        return ! Types::null($this->disk);
    }

    public function contents(): ?string
    {
        return $this->disk?->get($this->source);
    }

    public function mimeType(): ?string
    {
        return $this->disk?->mimeType($this->source);
    }

    public function isMimeType(string $mime): bool
    {
        return str($this->mimeType())->is($mime);
    }

    public function arguments(array $options = [], array $globals = []): Collection
    {
        $argumentsBase64 = File::extension($this->file);

        $argumentsJson = Base64::decode($argumentsBase64);

        $arguments = Json::decodeToCollection($argumentsJson);

        return $arguments->merge($this->optionsFromGlobals($globals))->merge($options);
    }

    public function url(array $options = [], array $globals = []): ?string
    {
        if (! $this->exists()) {
            return null;
        }

        $arguments = $this->arguments($options, $globals);

        if (! $arguments->count()) {
            return null;
        }

        $argumentsJson = Json::encode($arguments);

        $argumentsBase64 = Base64::encodeWithoutEnding($argumentsJson);

        return Url::withPath($this->builder($argumentsBase64));
    }

    protected function optionsFromGlobals(array $defaults): array
    {
        return collect_config('cdn.defaults')
            ->merge($defaults)
            ->filter(fn (array $defaults, string $mime) => $this->isMimeType($mime))
            ->last(default: []);
    }

    protected function findOnEnabledDisks(?Collection $disks = null): void
    {
        $disks ??= $this->enabledDisks(Sync::create(), Root::create());

        if (! $disks->count()) {
            return;
        }

        $publicAccessor = str(
            Url::pathFrom($disks->first()->url(DIRECTORY_SEPARATOR))->getPath()
        )->trim(DIRECTORY_SEPARATOR);

        $path = Condition::create($file = $this->builder())
            ->add(fn () => $file->after($publicAccessor), $publicAccessor->isNotEmpty())
            ->resolve();

        if (! $disks->first()->exists($path)) {
            $this->findOnEnabledDisks($disks->withoutFirst());

            return;
        }

        if (! $path->is($file) && ! $file->before(DIRECTORY_SEPARATOR)->is($publicAccessor)) {
            $this->findOnEnabledDisks($disks->withoutFirst());

            return;
        }

        if ($disks->first()->getVisibility($path) !== 'public') {
            return;
        }

        $this->source = $path;
        $this->disk = $disks->first();
    }

    protected function builder(?string $nameBuilder = null): Stringable
    {
        $name = collect([File::name($this->file), $nameBuilder])->filter()->toDotWord();

        return collect([
            File::dirname($this->path), $name,
        ])->toDirectory()->extension(File::extension($this->path))->ltrim(DIRECTORY_SEPARATOR);
    }

    protected function enabledDisks(DiskInterface ...$disks): Collection
    {
        $disks = collect($disks)->map(fn (DiskInterface $disk) => $disk->disk());

        return collect_config('cdn.disks')
            ->map(fn (string $disk) => Storage::disk($disk))
            ->push(...$disks)
            ->filter();
    }
}
