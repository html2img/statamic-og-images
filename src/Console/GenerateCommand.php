<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Console;

use Html2img\StatamicOgImages\Rendering\OgImageGenerator;
use Html2img\StatamicOgImages\Support\Fields;
use Html2img\StatamicOgImages\Support\Settings;
use Illuminate\Console\Command;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as Entries;

/**
 * Regenerates Open Graph images across enabled collections. Run it after
 * changing a template. `--force` ignores the input hash so every entry is
 * re-rendered.
 */
final class GenerateCommand extends Command
{
    protected $signature = 'og:generate {--collection=* : Limit to these collection handles} {--force : Regenerate even when inputs are unchanged}';

    protected $description = 'Regenerate Open Graph images across enabled collections.';

    public function handle(Settings $settings, OgImageGenerator $generator): int
    {
        $collections = $this->option('collection') ?: $settings->enabledCollections();

        if (empty($collections)) {
            $this->warn('No enabled collections. Enable one in the addon settings or pass --collection.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $total = 0;

        foreach ($collections as $handle) {
            if (! $settings->isEnabled($handle)) {
                $this->warn("Collection [{$handle}] is not enabled, skipping.");

                continue;
            }

            $entries = Entries::query()->where('collection', $handle)->get();
            $this->line("Generating for [{$handle}] ({$entries->count()} entries)...");

            foreach ($entries as $entry) {
                if ($this->shouldSkip($entry)) {
                    continue;
                }

                $url = $generator->generate($entry, $force);

                if ($url !== null) {
                    $total++;
                }
            }
        }

        $this->info("Done. {$total} image(s) generated.");

        return self::SUCCESS;
    }

    private function shouldSkip(Entry $entry): bool
    {
        return (bool) $entry->value(Fields::DISABLED) || filled($entry->value(Fields::CUSTOM));
    }
}
