<?php

declare(strict_types=1);

use Html2img\StatamicOgImages\Support\Settings;

it('uses the site default template and dimensions when nothing overrides them', function () {
    makeCollection('pages');
    $entry = makeEntry('pages', ['title' => 'About']);

    $resolved = app(Settings::class)->forEntry($entry);

    expect($resolved->template)->toBe('og-images::default')
        ->and($resolved->width)->toBe(1200)
        ->and($resolved->height)->toBe(630)
        ->and($resolved->dpi)->toBe(2);
});

it('applies a per-collection override over the site default', function () {
    config()->set('statamic-og-images.overrides', [
        'pages' => ['template' => 'og_pages', 'height' => 800],
    ]);

    makeCollection('pages');
    $entry = makeEntry('pages', ['title' => 'About']);

    $resolved = app(Settings::class)->forEntry($entry);

    expect($resolved->template)->toBe('og_pages')
        ->and($resolved->height)->toBe(800)
        ->and($resolved->width)->toBe(1200);
});

it('lets a per-entry template win over the collection and site defaults', function () {
    config()->set('statamic-og-images.overrides', ['pages' => ['template' => 'og_pages']]);

    makeCollection('pages');
    $entry = makeEntry('pages', ['title' => 'About', 'og_image_template' => 'og_special']);

    expect(app(Settings::class)->forEntry($entry)->template)->toBe('og_special');
});
