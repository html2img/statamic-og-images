<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Storage;

use Html2img\StatamicOgImages\Support\ResolvedSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\AssetContainer;

/**
 * Downloads a rendered image into a Statamic asset container so a site can run
 * without a runtime dependency on the CDN URL. Used when storage mode is
 * `asset`.
 */
final class AssetLocaliser
{
    /**
     * Download the image and store it in the configured container. Returns the
     * asset's URL, or null if no container is configured or the download fails.
     */
    public function localise(string $url, Entry $entry, ResolvedSettings $resolved): ?string
    {
        if (! $resolved->assetContainer) {
            return null;
        }

        $container = AssetContainer::find($resolved->assetContainer);

        if (! $container) {
            Log::warning('[og-images] Asset container not found: '.$resolved->assetContainer);

            return null;
        }

        try {
            $contents = Http::get($url)->throw()->body();
        } catch (\Throwable $e) {
            Log::error('[og-images] Could not download rendered image', ['message' => $e->getMessage()]);

            return null;
        }

        $path = 'og-images/'.$entry->id().'.'.$resolved->format;

        $container->disk()->put($path, $contents);

        $asset = $container->makeAsset($path);
        $asset->save();

        return $asset->url();
    }
}
