<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Support;

/**
 * Handles of the fields the addon manages on entries. These are injected into
 * enabled collections by the service provider and read by the pipeline.
 */
final class Fields
{
    public const URL = 'og_image_url';

    public const HASH = 'og_image_hash';

    public const TEMPLATE = 'og_image_template';

    public const HEADLINE = 'og_image_headline';

    public const SUBTITLE = 'og_image_subtitle';

    public const CUSTOM = 'og_image_custom';

    public const DISABLED = 'og_image_disabled';
}
