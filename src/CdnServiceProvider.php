<?php

namespace Mpietrucha\Cdn;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Mpietrucha\Cdn\Console\Commands;
use Mpietrucha\Cdn\Contracts\CachePathResolverInterface;
use Mpietrucha\Cdn\Contracts\CurrentFileResolverInterface;
use Mpietrucha\Cdn\Drivers;
use Mpietrucha\Cdn\Mixins;
use Mpietrucha\Cdn\Resolvers\DefaultCachePathResolver;
use Mpietrucha\Cdn\Resolvers\DefaultCurrentFileResolver;

class CdnServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/cdn.php' => config_path('cdn.php'),
        ], 'config');

        if (! config('cdn.enabled')) {
            return;
        }

        config([
            'app.asset_url' => Url::toString(),
        ]);

        collect_config('cdn.disks')->each($this->replaceFilesystemDisksUrl(...));

        Storage::mixin(new Components\Storage);

        Blade::component('cdn', Components\Blade::class);

        Blade::directive('cdn', function (string $expression) {
            return "<?php echo cdn($expression); ?>";
        });

        Drivers\ImageDriver::mixin(new Mixins\ImageDriverMixin);
        Drivers\TextDriver::mixin(new Mixins\TextDriverMixin);

        $this->loadRoutesFrom(__DIR__.'./../routes/web.php');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Commands\InstallCommand::class,
            Commands\ClearCacheCommand::class,
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'./../config/cdn.php', 'cdn');

        $this->app->bind(CurrentFileResolverInterface::class, DefaultCurrentFileResolver::class);
        $this->app->bind(CachePathResolverInterface::class, DefaultCachePathResolver::class);
    }

    protected function replaceFilesystemDisksUrl(string $disk): void
    {
        $configuration = collect_config("filesystems.disks.$disk");

        if (! $url = $configuration->get('url')) {
            return;
        }

        if (! $configuration->get('driver') === 'local') {
            return;
        }

        config([
            "filesystems.disks.$disk.url" => Url::pathFrom($url),
        ]);
    }
}
