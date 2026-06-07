<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Listeners;

use Html2img\StatamicOgImages\Jobs\GenerateOgImage;
use Html2img\StatamicOgImages\Support\Fields;
use Html2img\StatamicOgImages\Support\Settings;
use Statamic\Contracts\Entries\Entry;
use Statamic\Events\EntrySaved;

/**
 * Dispatches a generation job when an entry in an enabled collection is saved,
 * unless the entry opts out or supplies its own custom image.
 */
final class GenerateOnEntrySaved
{
    public function __construct(private readonly Settings $settings) {}

    public function handle(EntrySaved $event): void
    {
        $entry = $event->entry;

        if (! $this->shouldGenerate($entry)) {
            return;
        }

        GenerateOgImage::dispatch($entry->id());
    }

    public function shouldGenerate(Entry $entry): bool
    {
        if (! $this->settings->isEnabled((string) $entry->collectionHandle())) {
            return false;
        }

        if ($entry->value(Fields::DISABLED)) {
            return false;
        }

        $custom = $entry->value(Fields::CUSTOM);

        return blank($custom);
    }
}
