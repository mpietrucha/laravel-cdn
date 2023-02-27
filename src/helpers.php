<?php

use Mpietrucha\Cdn\Contracts\CurrentFileResolverInterface;
use Mpietrucha\Cdn\Url;

if (! function_exists('cdn')) {
    function cdn(string $path, array $options = [], array $globals = []): string
    {
        if (! config('cdn.enabled')) {
            return $path;
        }

        $path = Url::pathFrom($path)->getPath();

        $resolver = app(CurrentFileResolverInterface::class, compact('path'));

        return $resolver->url($options, $globals) ?? asset($path);
    }
}
