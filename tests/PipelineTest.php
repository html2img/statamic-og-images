<?php

declare(strict_types=1);

use Html2img\StatamicOgImages\Rendering\OgImageGenerator;
use Statamic\Facades\Entry;

it('renders the template to html and stores the returned url on the entry', function () {
    $history = [];
    fakeHttp([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'url' => 'https://i.html2img.com/x.png', 'credits_remaining' => 9]),
    ], $history);

    makeCollection('blog');
    $entry = makeEntry('blog', ['title' => 'Hello World']);

    $url = app(OgImageGenerator::class)->generate($entry);

    expect($url)->toBe('https://i.html2img.com/x.png');

    $body = lastRequestBody($history);
    expect($body['html'])->toContain('Hello World')
        ->and($body['width'])->toBe(1200)
        ->and($body['height'])->toBe(630)
        ->and($body['dpi'])->toBe(2);

    $fresh = Entry::find($entry->id());
    expect($fresh->value('og_image_url'))->toBe('https://i.html2img.com/x.png')
        ->and($fresh->value('og_image_hash'))->not->toBeNull();
});

it('skips regeneration when the inputs are unchanged', function () {
    $history = [];
    // Only one response is queued; a second API call would exhaust the mock and fail.
    fakeHttp([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'url' => 'https://i.html2img.com/x.png']),
    ], $history);

    makeCollection('blog');
    $entry = makeEntry('blog', ['title' => 'Hello']);

    $generator = app(OgImageGenerator::class);
    $first = $generator->generate($entry);
    $second = $generator->generate(Entry::find($entry->id()));

    expect($first)->toBe('https://i.html2img.com/x.png')
        ->and($second)->toBe('https://i.html2img.com/x.png')
        ->and($history)->toHaveCount(1);
});

it('regenerates when forced even if the inputs are unchanged', function () {
    $history = [];
    fakeHttp([
        jsonResponse(200, ['success' => true, 'id' => 'a', 'url' => 'https://i.html2img.com/1.png']),
        jsonResponse(200, ['success' => true, 'id' => 'b', 'url' => 'https://i.html2img.com/2.png']),
    ], $history);

    makeCollection('blog');
    $entry = makeEntry('blog', ['title' => 'Hello']);

    $generator = app(OgImageGenerator::class);
    $generator->generate($entry);
    $second = $generator->generate(Entry::find($entry->id()), force: true);

    expect($second)->toBe('https://i.html2img.com/2.png')
        ->and($history)->toHaveCount(2);
});

it('writes the url into the SEO field in seo-addon mode', function () {
    config()->set('statamic-og-images.integration', 'seo-addon');
    config()->set('statamic-og-images.seo_field', 'seo_image');

    $history = [];
    fakeHttp([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'url' => 'https://i.html2img.com/x.png']),
    ], $history);

    makeCollection('blog');
    $entry = makeEntry('blog', ['title' => 'Hello']);

    app(OgImageGenerator::class)->generate($entry);

    $fresh = Entry::find($entry->id());
    expect($fresh->value('seo_image'))->toBe('https://i.html2img.com/x.png')
        ->and($fresh->value('og_image_url'))->toBe('https://i.html2img.com/x.png');
});

it('degrades cleanly for an entry without author, date or image', function () {
    $history = [];
    fakeHttp([
        jsonResponse(200, ['success' => true, 'id' => 'x', 'url' => 'https://i.html2img.com/x.png']),
    ], $history);

    makeCollection('pages');
    $entry = makeEntry('pages', ['title' => 'Plain Page']);

    app(OgImageGenerator::class)->generate($entry);

    $html = lastRequestBody($history)['html'];
    expect($html)->toContain('Plain Page')
        ->and($html)->not->toContain('class="figure"')
        ->and($html)->not->toContain('&bull;');
});
