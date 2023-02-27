<?php

namespace Mpietrucha\Cdn\Factory;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use Mpietrucha\Cdn\Contracts\DriverInterface;

abstract class Driver implements DriverInterface
{
    use ForwardsCalls;
    use Macroable {
        __call as macroCall;
    }

    protected ?object $forwardsTo = null;

    public function __call(string $method, array $arguments): mixed
    {
        if (self::hasMacro($method)) {
            return $this->macroCall($method, $arguments);
        }

        if (! $this->forwardsTo) {
            throw new Exception('Cannot forward call to unknow location');
        }

        return $this->forwardCallTo($this->forwardsTo, $method, $arguments);
    }

    protected function forwardTo(object $forwardTo): self
    {
        $this->forwardTo = $forwardTo;

        return $this;
    }

    protected function arguments(Collection $arguments): Collection
    {
        return $arguments->map(fn (string|array $arguments) => Arr::wrap($arguments));
    }
}
