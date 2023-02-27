<?php

namespace Mpietrucha\Cdn\Components;

use Closure;
use Illuminate\View\Component;
use Mpietrucha\Cdn\Url;

class Blade extends Component
{
    public function __construct(public array $options = [])
    {
    }

    public function render(): Closure
    {
        $class = self::class;

        return fn (array $data) => '{!! '.$class.'::findAndReplaceAllLinks($slot, $options) !!}';
    }

    public static function findAndReplaceAllLinks(string $content, array $attributes): string
    {
        if (! config('cdn.enabled')) {
            return $content;
        }

        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $content, $matches);

        $urls = collect(head($matches) ?? null)->values()->unique()->filter(fn (string $url) => Url::hostsMatches($url));

        return str($content)->replace($urls->toArray(), $urls->map(fn (string $url) => cdn($url, globals: $attributes)));
    }
}
