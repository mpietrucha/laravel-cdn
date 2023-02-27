<?php

namespace Mpietrucha\Cdn\Drivers;

use Mpietrucha\Cdn\Contracts\CurrentFileResolverInterface;
use Mpietrucha\Cdn\Factory\Driver;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use MatthiasMullie\Minify\Minify;
use MatthiasMullie\Minify\JS;
use MatthiasMullie\Minify\CSS;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Facades\File;
use Mpietrucha\Support\Condition;
use Illuminate\Support\Arr;

class TextDriver extends Driver
{
    protected string $contents;

    protected string $mimeType;

    protected ?Minify $minify = null;

    protected const HANDLERS = [
        'js' => JS::class,
        'css' => CSS::class
    ];

    public function __construct(protected CurrentFileResolverInterface $file)
    {
        $this->contents = $file->contents();

        $handler = Arr::get(self::HANDLERS, File::extension($file->url()));

        $this->minify = Condition::create()->add(fn () => new $handler($file->contents()), $file)->resolve();

        $this->mimeType = $file->mimeType();

        $this->arguments($file->arguments())->each(function (array $arguments, string $name) {
            $this->contents = rescue(fn () => $this->$name(...$arguments)) ?? $this->contents;
        });
    }

    public static function handles(): array
    {
        return ['text/*'];
    }

    public function stream(): StreamInterface
    {
        return Utils::streamFor($this->contents);
    }

    public function response(): Response
    {
        return response($this->contents)->withHeaders([
            'Content-Type' => $this->mimeType,
            'Content-Length' => mb_strlen($this->contents, '8bit'),
            'Content-Disposition' => 'inline'
        ]);
    }
}
