<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Rendering;

use Html2img\Exception\Html2imgException;
use Html2img\Html2imgClient;
use Html2img\Request\HtmlRequest;
use Html2img\StatamicOgImages\Storage\AssetLocaliser;
use Html2img\StatamicOgImages\Support\Fields;
use Html2img\StatamicOgImages\Support\ResolvedSettings;
use Html2img\StatamicOgImages\Support\Settings;
use Illuminate\Support\Facades\Log;
use Statamic\Contracts\Entries\Entry;

/**
 * The generation pipeline: resolve settings, render the template against an
 * entry's augmented data, send the HTML to the API, and store the resulting
 * image on the entry.
 */
final class OgImageGenerator
{
    public function __construct(
        private readonly Html2imgClient $client,
        private readonly TemplateRenderer $renderer,
        private readonly Settings $settings,
        private readonly AssetLocaliser $localiser,
    ) {}

    /**
     * Generate (or reuse) the Open Graph image for an entry and store it.
     * Returns the stored URL, or null when generation failed.
     */
    public function generate(Entry $entry, bool $force = false): ?string
    {
        $resolved = $this->settings->forEntry($entry);
        $html = $this->renderHtml($entry, $resolved);
        $hash = $this->hash($html, $resolved);

        if (! $force && $this->isUnchanged($entry, $hash)) {
            return (string) $entry->value(Fields::URL);
        }

        try {
            $response = $this->client->html(new HtmlRequest(
                html: $html,
                width: $resolved->width,
                height: $resolved->height,
                dpi: $resolved->dpi,
            ));
        } catch (Html2imgException $e) {
            Log::error('[og-images] Render failed for entry '.$entry->id(), [
                'entry' => $entry->id(),
                'code' => $e->errorCode(),
                'status' => $e->statusCode(),
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        $url = $response->url;

        if ($url === null) {
            return null;
        }

        if ($resolved->isAssetStorage()) {
            $url = $this->localiser->localise($url, $entry, $resolved) ?? $url;
        }

        $this->store($entry, $resolved, $url, $hash);

        return $url;
    }

    /**
     * Render the entry's template to an HTML string without calling the API.
     */
    public function renderHtml(Entry $entry, ResolvedSettings $resolved): string
    {
        return $this->renderer->render($resolved->template, $this->data($entry));
    }

    /**
     * Render a preview using representative sample data, for when there is no
     * entry to render against.
     */
    public function renderSample(ResolvedSettings $resolved): string
    {
        return $this->renderer->render($resolved->template, $this->sampleData());
    }

    /**
     * The data passed to the template for a given entry: its augmented fields,
     * plus the headline/subtitle overrides and site details.
     *
     * @return array<string, mixed>
     */
    public function data(Entry $entry): array
    {
        $data = $entry->toAugmentedArray();

        $headline = $entry->value(Fields::HEADLINE);
        $subtitle = $entry->value(Fields::SUBTITLE);

        $data['og_headline'] = is_string($headline) && $headline !== '' ? $headline : ($data['title'] ?? $entry->value('title'));
        $data['og_subtitle'] = is_string($subtitle) && $subtitle !== '' ? $subtitle : null;
        $data['site_name'] = $this->settings->siteName();
        $data['site_logo'] = $this->settings->siteLogo();

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function sampleData(): array
    {
        return [
            'title' => 'How real Chrome rendering changes social images',
            'og_headline' => 'How real Chrome rendering changes social images',
            'og_subtitle' => 'A worked example of the Open Graph pipeline',
            'author_name' => 'Jamie Rivera',
            'date' => now(),
            'site_name' => $this->settings->siteName(),
            'site_logo' => $this->settings->siteLogo(),
        ];
    }

    public function hash(string $html, ResolvedSettings $resolved): string
    {
        return sha1(implode('|', [
            $resolved->template,
            $resolved->width.'x'.$resolved->height.'@'.$resolved->dpi,
            $html,
        ]));
    }

    private function isUnchanged(Entry $entry, string $hash): bool
    {
        return $entry->value(Fields::HASH) === $hash
            && is_string($entry->value(Fields::URL))
            && $entry->value(Fields::URL) !== '';
    }

    private function store(Entry $entry, ResolvedSettings $resolved, string $url, string $hash): void
    {
        $entry->set(Fields::URL, $url)->set(Fields::HASH, $hash);

        if ($resolved->isSeoAddonMode() && $resolved->seoField) {
            $entry->set($resolved->seoField, $url);
        }

        $entry->saveQuietly();
    }
}
