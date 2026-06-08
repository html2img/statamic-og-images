<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Fieldtypes;

use Html2img\StatamicOgImages\Support\Settings;
use Statamic\Fields\Fieldtype;

/**
 * A read-only publish-screen banner that surfaces the addon's connection
 * state. It warns prominently when no API key is configured and tells the
 * editor where to get one, so the missing-key case is impossible to miss on
 * the settings screen and on enabled entries. The UI lives in the addon's Vue
 * component, built through Vite.
 */
class OgImageStatus extends Fieldtype
{
    protected $icon = 'information';

    protected $selectable = false;

    /**
     * Data made available to the Vue component as `meta`.
     *
     * @return array<string, mixed>
     */
    public function preload(): array
    {
        return [
            'has_api_key' => app(Settings::class)->hasApiKey(),
            'register_url' => 'https://app.html2img.com/register',
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
