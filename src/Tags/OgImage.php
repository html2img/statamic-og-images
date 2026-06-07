<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Tags;

use Html2img\StatamicOgImages\Rendering\ImageResolver;
use Html2img\StatamicOgImages\Support\Settings;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as Entries;
use Statamic\Fields\Value;
use Statamic\Tags\Tags;

/**
 * Frontend output for the standalone integration mode.
 *
 * `{{ og_image }}` outputs the resolved image URL.
 * `{{ og_image:meta }}` outputs the Open Graph and Twitter meta tags.
 */
class OgImage extends Tags
{
    protected static $handle = 'og_image';

    /**
     * {{ og_image }} -> the resolved URL for the current entry.
     */
    public function index(): ?string
    {
        return $this->resolver()->resolve($this->currentEntry());
    }

    /**
     * {{ og_image:meta }} -> the social meta tags.
     */
    public function meta(): string
    {
        $entry = $this->currentEntry();
        $url = $this->resolver()->resolve($entry);

        if ($url === null || $url === '') {
            return '';
        }

        $settings = app(Settings::class);
        $resolved = $entry ? $settings->forEntry($entry) : $settings->forDefault();

        $alt = $entry?->value('title');
        $alt = is_string($alt) && $alt !== '' ? $alt : $settings->siteName();

        $type = $resolved->format === 'png' ? 'image/png' : 'image/'.$resolved->format;

        $tags = [
            $this->metaProperty('og:image', $url),
            $this->metaProperty('og:image:width', (string) $resolved->width),
            $this->metaProperty('og:image:height', (string) $resolved->height),
            $this->metaProperty('og:image:type', $type),
            $this->metaProperty('og:image:alt', $alt),
            $this->metaName('twitter:card', 'summary_large_image'),
            $this->metaName('twitter:image', $url),
        ];

        return implode("\n", $tags);
    }

    private function currentEntry(): ?Entry
    {
        $id = $this->context->get('id');

        if ($id instanceof Value) {
            $id = $id->value();
        }

        if (is_string($id) && $id !== '') {
            return Entries::find($id);
        }

        return null;
    }

    private function resolver(): ImageResolver
    {
        return app(ImageResolver::class);
    }

    private function metaProperty(string $property, string $content): string
    {
        return '<meta property="'.e($property).'" content="'.e($content).'">';
    }

    private function metaName(string $name, string $content): string
    {
        return '<meta name="'.e($name).'" content="'.e($content).'">';
    }
}
