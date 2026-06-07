<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Jobs;

use Html2img\StatamicOgImages\Rendering\OgImageGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Statamic\Facades\Entry;

/**
 * Generates an entry's Open Graph image off the request cycle. The entry is
 * referenced by id so the payload stays small and always reflects the latest
 * saved state.
 */
final class GenerateOgImage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $entryId,
        public readonly bool $force = false,
    ) {
        $queue = config('statamic-og-images.queue');

        if (is_string($queue) && $queue !== '') {
            $this->onQueue($queue);
        }
    }

    public function handle(OgImageGenerator $generator): void
    {
        $entry = Entry::find($this->entryId);

        if (! $entry) {
            return;
        }

        $generator->generate($entry, $this->force);
    }
}
