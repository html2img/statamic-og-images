<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Listeners;

use Html2img\StatamicOgImages\Support\Fields;
use Html2img\StatamicOgImages\Support\Settings;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Events\EntryBlueprintFound;

/**
 * Injects the addon's per-entry control fields into the entry blueprints of
 * enabled collections, so editors get template choice, text overrides, a
 * custom-image upload and a disable toggle without editing blueprints by hand.
 */
final class InjectOgImageFields
{
    public function __construct(private readonly Settings $settings) {}

    public function handle(EntryBlueprintFound $event): void
    {
        $handle = $this->collectionHandle($event);

        if ($handle === null || ! $this->settings->isEnabled($handle)) {
            return;
        }

        $event->blueprint->ensureFieldsInTab($this->fields(), 'og_images');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function fields(): array
    {
        return [
            Fields::TEMPLATE => [
                'type' => 'text',
                'display' => 'Template',
                'instructions' => 'The view to render. Leave blank to use the site default.',
            ],
            Fields::HEADLINE => [
                'type' => 'text',
                'display' => 'Headline override',
            ],
            Fields::SUBTITLE => [
                'type' => 'text',
                'display' => 'Subtitle override',
            ],
            Fields::CUSTOM => [
                'type' => 'assets',
                'max_files' => 1,
                'mode' => 'list',
                'display' => 'Custom image',
                'instructions' => 'Upload an image to use as is and bypass generation.',
            ],
            Fields::DISABLED => [
                'type' => 'toggle',
                'display' => 'Disable automatic generation',
            ],
            'og_image_preview' => [
                'type' => 'og_image_preview',
                'display' => 'Preview',
            ],
        ];
    }

    private function collectionHandle(EntryBlueprintFound $event): ?string
    {
        if ($event->entry instanceof Entry) {
            return $event->entry->collectionHandle();
        }

        $parent = $event->blueprint->parent();

        if ($parent instanceof Collection) {
            return $parent->handle();
        }

        if ($parent instanceof Entry) {
            return $parent->collectionHandle();
        }

        return null;
    }
}
