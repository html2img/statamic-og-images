<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages;

use GuzzleHttp\ClientInterface;
use Html2img\Html2imgClient;
use Html2img\StatamicOgImages\Console\GenerateCommand;
use Html2img\StatamicOgImages\Fieldtypes\OgImagePreview;
use Html2img\StatamicOgImages\Fieldtypes\OgImageStatus;
use Html2img\StatamicOgImages\Listeners\GenerateOnEntrySaved;
use Html2img\StatamicOgImages\Listeners\InjectOgImageFields;
use Html2img\StatamicOgImages\Rendering\OgImageGenerator;
use Html2img\StatamicOgImages\Support\Settings;
use Html2img\StatamicOgImages\Tags\OgImage;
use Illuminate\Contracts\Foundation\Application;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Addon;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $viewNamespace = 'og-images';

    protected $tags = [
        OgImage::class,
    ];

    protected $fieldtypes = [
        OgImagePreview::class,
        OgImageStatus::class,
    ];

    protected $commands = [
        GenerateCommand::class,
    ];

    protected $listen = [
        EntrySaved::class => [GenerateOnEntrySaved::class],
        EntryBlueprintFound::class => [InjectOgImageFields::class],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'resources/js/cp.js',
    ];

    public function register()
    {
        $this->app->singleton(Settings::class);

        $this->app->singleton(Html2imgClient::class, function (Application $app): Html2imgClient {
            $settings = $app->make(Settings::class);

            return new Html2imgClient(
                apiKey: (string) ($settings->apiKey() ?? ''),
                baseUri: (string) $settings->get('base_uri', Html2imgClient::DEFAULT_BASE_URI),
                httpClient: $this->injectedHttpClient($app),
            );
        });

        $this->app->singleton(OgImageGenerator::class);
    }

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../resources/views/default.antlers.html' => resource_path('views/vendor/og-images/default.antlers.html'),
        ], 'statamic-og-images-views');

        $this->registerNav();
    }

    private function registerNav(): void
    {
        Nav::extend(function ($nav) {
            $url = Addon::get(Settings::PACKAGE)?->settingsUrl();

            if (! $url) {
                return;
            }

            $nav->create('Open Graph Images')
                ->section('Tools')
                ->url($url)
                ->icon('assets');
        });
    }

    private function injectedHttpClient(Application $app): ?ClientInterface
    {
        if (! $app->bound('og-images.http')) {
            return null;
        }

        $client = $app->make('og-images.http');

        return $client instanceof ClientInterface ? $client : null;
    }
}
