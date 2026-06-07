<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Http\Controllers;

use Html2img\StatamicOgImages\Rendering\OgImageGenerator;
use Html2img\StatamicOgImages\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as Entries;
use Statamic\Http\Controllers\CP\CpController;

/**
 * Renders the resolved template at the configured dimensions. It is registered
 * as a Control Panel route, so it is gated to authenticated users. It needs no
 * API key, since the browser renders the same HTML the API would. Used both
 * directly in a browser for the design loop and embedded as an iframe in the
 * Control Panel.
 */
final class PreviewController extends CpController
{
    public function show(Request $request, OgImageGenerator $generator, Settings $settings): Response
    {
        $entry = $this->findEntry($request->query('entry'));

        if ($entry) {
            $resolved = $settings->forEntry($entry);
            $html = $generator->renderHtml($entry, $resolved);
        } else {
            $resolved = $settings->forDefault();
            $html = $generator->renderSample($resolved);
        }

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function findEntry(mixed $ref): ?Entry
    {
        if (! is_string($ref) || $ref === '') {
            return null;
        }

        return Entries::find($ref) ?? Entries::query()->where('slug', $ref)->first();
    }
}
