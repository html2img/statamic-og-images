<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Support;

/**
 * The settings that apply to a single render, after the site, collection and
 * entry cascade has been resolved.
 */
final readonly class ResolvedSettings
{
    public function __construct(
        public string $template,
        public int $width,
        public int $height,
        public int $dpi,
        public string $format,
        public string $storageMode,
        public string $integrationMode,
        public ?string $seoField,
        public ?string $assetContainer,
    ) {}

    public function isAssetStorage(): bool
    {
        return $this->storageMode === 'asset';
    }

    public function isSeoAddonMode(): bool
    {
        return $this->integrationMode === 'seo-addon';
    }
}
