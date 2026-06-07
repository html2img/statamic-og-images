<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Support;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Addon;

/**
 * Resolves the addon's settings through the cascade: a site default (config,
 * overlaid by the Control Panel settings screen), then a per-collection
 * override, then a per-entry override.
 */
final class Settings
{
    public const PACKAGE = 'html2img/statamic-og-images';

    /**
     * A setting value, preferring the Control Panel value and falling back to
     * config (which itself falls back to the environment).
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $stored = $this->stored($key);

        if ($stored !== null && $stored !== '' && $stored !== []) {
            return $stored;
        }

        return config('statamic-og-images.'.$key, $default);
    }

    /**
     * The API key. The environment is canonical; the Control Panel value is an
     * optional convenience stored in gitignored storage.
     */
    public function apiKey(): ?string
    {
        $env = config('statamic-og-images.api_key');

        if (is_string($env) && $env !== '') {
            return $env;
        }

        $stored = $this->stored('api_key');

        return is_string($stored) && $stored !== '' ? $stored : null;
    }

    public function hasApiKey(): bool
    {
        return $this->apiKey() !== null;
    }

    /**
     * @return list<string>
     */
    public function enabledCollections(): array
    {
        return array_values(array_filter(array_map(
            static fn ($handle): string => (string) $handle,
            (array) $this->get('collections', []),
        )));
    }

    public function isEnabled(string $collection): bool
    {
        return in_array($collection, $this->enabledCollections(), true);
    }

    public function integrationMode(): string
    {
        return (string) $this->get('integration', 'standalone');
    }

    public function storageMode(): string
    {
        return (string) $this->get('storage', 'cdn');
    }

    public function defaultImage(): ?string
    {
        $value = $this->get('default_image');

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function siteName(): string
    {
        $value = $this->get('site_name');

        return is_string($value) && $value !== '' ? $value : (string) config('app.name');
    }

    public function siteLogo(): ?string
    {
        $value = $this->get('site_logo');

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * The base settings with no entry context, used for previews and the tag
     * when there is no current entry.
     */
    public function forDefault(): ResolvedSettings
    {
        return $this->resolve(null, null);
    }

    /**
     * The settings for a specific entry, applying collection and entry
     * overrides on top of the site defaults.
     */
    public function forEntry(Entry $entry): ResolvedSettings
    {
        return $this->resolve($entry->collectionHandle(), $entry);
    }

    private function resolve(?string $collection, ?Entry $entry): ResolvedSettings
    {
        $override = $collection ? $this->collectionOverride($collection) : [];

        $template = $this->entryTemplate($entry)
            ?? ($override['template'] ?? null)
            ?? (string) $this->get('default_template', 'og-images::default');

        return new ResolvedSettings(
            template: (string) $template,
            width: (int) ($override['width'] ?? $this->get('width', 1200)),
            height: (int) ($override['height'] ?? $this->get('height', 630)),
            dpi: (int) ($override['dpi'] ?? $this->get('dpi', 2)),
            format: (string) ($override['format'] ?? $this->get('format', 'png')),
            storageMode: $this->storageMode(),
            integrationMode: $this->integrationMode(),
            seoField: ($field = $this->get('seo_field', 'og_image')) ? (string) $field : null,
            assetContainer: ($container = $this->get('asset_container')) ? (string) $container : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function collectionOverride(string $collection): array
    {
        $overrides = (array) $this->get('overrides', []);

        return is_array($overrides[$collection] ?? null) ? $overrides[$collection] : [];
    }

    private function entryTemplate(?Entry $entry): ?string
    {
        if (! $entry) {
            return null;
        }

        $value = $entry->value(Fields::TEMPLATE);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * The actually-saved Control Panel value for a key, or null. Reads the raw
     * stored settings rather than the merged blueprint defaults, so an unsaved
     * field falls through to config instead of shadowing it.
     */
    private function stored(string $key): mixed
    {
        $settings = Addon::get(self::PACKAGE)?->settings();

        if (! $settings) {
            return null;
        }

        $raw = $settings->raw();

        return is_array($raw) ? ($raw[$key] ?? null) : null;
    }
}
