<?php

namespace Mpietrucha\Cdn\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Stringable;
use Mpietrucha\Cdn\Url;
use Mpietrucha\Support\Concerns\HasVendor;
use Symfony\Component\Finder\SplFileInfo;

class InstallCommand extends Command
{
    use HasVendor;

    protected $signature = 'cdn:install';

    protected $description = 'Build CDN service to use with configured subdomain and cors.';

    protected ?Collection $nginxConfigs = null;

    protected const NGINX_CONFIGS = ['context-http.conf', 'context-server.conf'];

    protected const LINES_SERVER_DECLARATION = 'server_name';

    protected const LINES_CORS_DECLARATION = 'map $http_origin $cdn_cors';

    public function handle(): void
    {
        $this->components->task('Looking for nginx configuration.', function () {
            return $this->findNginxConfigs();
        });

        if (! $this->nginxConfigs) {
            $this->newLine()->error('Nginx config is missing. You should try install this package again.');

            return;
        }

        $this->components->task('Setting nginx cdn server name.', function () {
            return $this->withLineIn('context-server.conf', self::LINES_SERVER_DECLARATION, Url::getHost());
        });

        $this->components->task('Setting nginx cors.', function () {
            $cors = $this->buildCorsDeclaration();

            return $this->withLineIn('context-http.conf', self::LINES_CORS_DECLARATION, $cors, null);
        });
    }

    protected function findNginxConfigs(): bool
    {
        $configs = File::collectAllFiles($this->vendor()->path())->filter(fn (SplFileInfo $file) => in_array($file->getFileName(), self::NGINX_CONFIGS));

        if ($configs->count() !== count(self::NGINX_CONFIGS)) {
            return false;
        }

        $this->nginxConfigs = $configs;

        return true;
    }

    protected function withLineIn(string $configName, string $line, string $value, ?string $ending = ';'): bool
    {
        $config = $this->nginxConfigs->first(fn (SplFileInfo $config) => $config->getFileName() === $configName);

        if (! $config) {
            return false;
        }

        $newLineDeclaration = "$line $value$ending";

        $lines = File::lines($config)->collect()->toStringable()->map(function (Stringable $currentLine) use ($line, $newLineDeclaration) {
            if (! $currentLine->startsWith($line)) {
                return $currentLine;
            }

            return $newLineDeclaration;
        });

        File::put($config, $lines->join(PHP_EOL));

        return $lines->first(fn (string $line) => $line === $newLineDeclaration);
    }

    protected function buildCorsDeclaration(string $any = '*'): string
    {
        $origins = collect(config('cors.allowed_origins'));

        if ($origins->contains($any)) {
            return $this->buildCorsMapDeclaration('default $http_origin;');
        }

        $origins = $origins->map(fn (string $origin) => '~(http|https)://'.$origin.' $http_origin;');

        return $this->buildCorsMapDeclaration($origins->join(' '));
    }

    protected function buildCorsMapDeclaration(string $origins): string
    {
        return collect(['{', $origins, '}'])->toWords();
    }
}
