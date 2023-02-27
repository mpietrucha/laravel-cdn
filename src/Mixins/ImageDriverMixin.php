<?php

namespace Mpietrucha\Cdn\Mixins;

use Closure;
use Intervention\Image\Constraint;

class ImageDriverMixin
{
    public function resize(): Closure
    {
        return function (?int $width = null, ?int $height = null, bool $aspect = false, bool $upsize = false) {
            return $this->image->resize($width, $height, function (Constraint $constraint) use ($aspect, $upsize) {
                if ($aspect) {
                    $constraint->aspectRatio();
                }

                if ($upsize) {
                    $constraint->upsize();
                }
            });
        };
    }

    public function width(): Closure
    {
        return fn (int $width) => $this->resize($width, null, true);
    }

    public function height(): Closure
    {
        return fn (int $height) => $this->resize(null, $height, true);
    }

    public function placeholder(): Closure
    {
        return fn () => $this->resize(1, 1);
    }
}
