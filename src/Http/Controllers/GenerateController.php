<?php

declare(strict_types=1);

namespace Html2img\StatamicOgImages\Http\Controllers;

use Html2img\StatamicOgImages\Rendering\OgImageGenerator;
use Illuminate\Http\Request;
use Statamic\Facades\Entry as Entries;
use Statamic\Http\Controllers\CP\CpController;

/**
 * Triggers a real render through the API for a single entry and returns the
 * resulting PNG URL. Backs the "Generate" button on the entry publish screen,
 * the parity check between the browser preview and the rendered image.
 */
final class GenerateController extends CpController
{
    /**
     * @return array<string, string|null>
     */
    public function __invoke(Request $request, OgImageGenerator $generator): array
    {
        $entry = Entries::find((string) $request->input('entry'));

        if ($entry === null) {
            abort(404, 'Entry not found.');
        }

        $url = $generator->generate($entry, force: true);

        abort_if($url === null, 422, 'Generation failed. Check your API key and the logs.');

        return ['url' => $url];
    }
}
