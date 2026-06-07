<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | API key
    |--------------------------------------------------------------------------
    |
    | Your HTML to Image API key. The .env value is the canonical source. The
    | Control Panel settings screen offers an optional override, which is
    | stored in storage/ (gitignored). Issue a key at
    | https://app.html2img.com/register.
    |
    */

    'api_key' => env('HTML2IMG_API_KEY'),

    'base_uri' => env('HTML2IMG_BASE_URI', 'https://app.html2img.com'),

    /*
    |--------------------------------------------------------------------------
    | Default template and dimensions
    |--------------------------------------------------------------------------
    |
    | The view rendered into the Open Graph image. Publish the bundled default
    | with `php artisan vendor:publish --tag=statamic-og-images-views` and
    | point this at your own view, for example 'og_image'. Antlers and Blade
    | are both supported.
    |
    */

    'default_template' => 'og-images::default',

    'width' => 1200,

    'height' => 630,

    'dpi' => 2,

    'format' => 'png',

    /*
    |--------------------------------------------------------------------------
    | Enabled collections
    |--------------------------------------------------------------------------
    |
    | Entries in these collections generate an Open Graph image on save.
    |
    */

    'collections' => [],

    /*
    |--------------------------------------------------------------------------
    | Per-collection overrides
    |--------------------------------------------------------------------------
    |
    | Optionally override the template or dimensions per collection, keyed by
    | collection handle, for example:
    |
    | 'overrides' => [
    |     'products' => ['template' => 'og_product', 'height' => 800],
    | ],
    |
    */

    'overrides' => [],

    /*
    |--------------------------------------------------------------------------
    | Storage mode
    |--------------------------------------------------------------------------
    |
    | 'cdn'   stores the returned i.html2img.com URL on the entry.
    | 'asset' downloads the PNG into the asset container below and stores that.
    |
    */

    'storage' => 'cdn',

    'asset_container' => null,

    /*
    |--------------------------------------------------------------------------
    | Integration mode
    |--------------------------------------------------------------------------
    |
    | 'standalone' lets the {{ og_image:meta }} tag output the social tags.
    | 'seo-addon'  writes the URL into the field your SEO addon reads instead.
    |
    */

    'integration' => 'standalone',

    'seo_field' => 'og_image',

    /*
    |--------------------------------------------------------------------------
    | Site defaults
    |--------------------------------------------------------------------------
    |
    | A site name and logo passed to every template, plus a fallback image URL
    | used by the tag when an entry has no generated image.
    |
    */

    'site_name' => env('OG_SITE_NAME'),

    'site_logo' => null,

    'default_image' => null,

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | The queue connection used for generation jobs. Null uses the default.
    |
    */

    'queue' => null,

];
