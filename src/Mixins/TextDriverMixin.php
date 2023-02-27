<?php

namespace Mpietrucha\Cdn\Mixins;

use Closure;

class TextDriverMixin
{
    public function minify(): Closure
    {
        return fn () => $this->minify?->minify();
    }

    public function gzip(): Closure
    {
        return fn () => $this->minify?->gzip();
    }
}
