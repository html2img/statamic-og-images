<?php

declare(strict_types=1);

use Html2img\StatamicOgImages\Tags\OgImage;

/**
 * @param  array<string, mixed>  $context
 */
function ogImageTag(array $context): OgImage
{
    return tap(new OgImage)->setContext($context)->setParameters([]);
}

it('outputs Open Graph and Twitter meta tags for the current entry', function () {
    makeCollection('blog');
    $entry = makeEntry('blog', ['title' => 'Hello World']);
    $entry->set('og_image_url', 'https://i.html2img.com/x.png')->saveQuietly();

    $output = ogImageTag($entry->toAugmentedArray())->meta();

    expect($output)->toContain('<meta property="og:image" content="https://i.html2img.com/x.png">')
        ->and($output)->toContain('<meta property="og:image:width" content="1200">')
        ->and($output)->toContain('<meta property="og:image:height" content="630">')
        ->and($output)->toContain('<meta property="og:image:type" content="image/png">')
        ->and($output)->toContain('<meta property="og:image:alt" content="Hello World">')
        ->and($output)->toContain('<meta name="twitter:card" content="summary_large_image">')
        ->and($output)->toContain('<meta name="twitter:image" content="https://i.html2img.com/x.png">');
});

it('falls back to the site default image when the entry has none', function () {
    config()->set('statamic-og-images.default_image', 'https://cdn.example.com/site-default.png');

    makeCollection('pages');
    $entry = makeEntry('pages', ['title' => 'About']);

    $output = ogImageTag($entry->toAugmentedArray())->meta();

    expect($output)->toContain('content="https://cdn.example.com/site-default.png"');
});

it('outputs nothing when there is no image anywhere', function () {
    makeCollection('pages');
    $entry = makeEntry('pages', ['title' => 'About']);

    expect(ogImageTag($entry->toAugmentedArray())->meta())->toBe('');
});

it('prefers a custom image over the generated one', function () {
    config()->set('statamic-og-images.default_image', 'https://cdn.example.com/site-default.png');

    makeCollection('blog');
    $entry = makeEntry('blog', ['title' => 'Hello']);
    $entry->set('og_image_url', 'https://i.html2img.com/generated.png')
        ->set('og_image_custom', 'https://cdn.example.com/custom.png')
        ->saveQuietly();

    $output = ogImageTag($entry->toAugmentedArray())->meta();

    expect($output)->toContain('content="https://cdn.example.com/custom.png"')
        ->and($output)->not->toContain('generated.png');
});
