<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Rendering;

use Html2img\StatamicOgImages\Support\Fields;
use Html2img\StatamicOgImages\Support\Settings;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Entry;

/**
 * Resolves the Open Graph image URL for an entry through the cascade: a custom
 * upload that bypasses generation, then the generated image, then the
 * site-level fallback image.
 */
final class ImageResolver
{
    public function __construct(private readonly Settings $settings) {}

    public function resolve(?Entry $entry): ?string
    {
        if ($entry) {
            if ($custom = $this->assetUrl($entry->value(Fields::CUSTOM))) {
                return $custom;
            }

            $generated = $entry->value(Fields::URL);

            if (is_string($generated) && $generated !== '') {
                return $generated;
            }
        }

        return $this->settings->defaultImage();
    }

    private function assetUrl(mixed $value): ?string
    {
        if ($value instanceof Asset) {
            return $value->url();
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }
}
