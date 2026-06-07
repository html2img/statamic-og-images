<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Fieldtypes;

use Html2img\StatamicOgImages\Support\Settings;
use Statamic\Fields\Fieldtype;

/**
 * A read-only publish-screen field that embeds the preview iframe for the
 * current entry and offers a "Generate" button to run a real render. The UI
 * lives in the addon's Vue component, built through Vite.
 */
class OgImagePreview extends Fieldtype
{
    protected $icon = 'image';

    protected $selectable = false;

    /**
     * Data made available to the Vue component as `meta`.
     *
     * @return array<string, mixed>
     */
    public function preload(): array
    {
        $settings = app(Settings::class);

        return [
            'preview_url' => cp_route('og-images.preview'),
            'generate_url' => cp_route('og-images.generate'),
            'width' => (int) $settings->get('width', 1200),
            'height' => (int) $settings->get('height', 630),
        ];
    }

    public function preProcess($data): mixed
    {
        return $data;
    }

    public function process($data): mixed
    {
        return $data;
    }
}
