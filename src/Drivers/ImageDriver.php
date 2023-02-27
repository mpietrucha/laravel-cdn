<?php

namespace Mpietrucha\Cdn\Drivers;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Mpietrucha\Cdn\Contracts\CurrentFileResolverInterface;
use Mpietrucha\Cdn\Factory\Driver;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response;

class ImageDriver extends Driver
{
    protected Image $image;

    protected array $arguments;

    public function __construct(CurrentFileResolverInterface $file)
    {
        $arguments = $file->arguments();

        $this->arguments = [$arguments->pull('format'), $arguments->pull('quality')];

        $this->image = with(new ImageManager(['driver' => config('cdn.image')]), function (ImageManager $manager) use ($file) {
            return $manager->make($file->contents());
        });

        $this->forwardTo($this->image);

        $this->arguments($arguments)->each(fn (array $arguments, string $name) => rescue(fn () => $this->$name(...$arguments)));
    }

    public static function handles(): array
    {
        return ['image/*'];
    }

    public function stream(): StreamInterface
    {
        return $this->image->stream(...$this->arguments);
    }

    public function response(): Response
    {
        return $this->image->response(...$this->arguments);
    }
}
