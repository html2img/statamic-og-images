<?php

declare(strict_types=1);

use Html2img\StatamicOgImages\Jobs\GenerateOgImage;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

it('dispatches a job when an entry in an enabled collection is saved', function () {
    enableCollections(['blog']);
    makeCollection('blog');

    makeEntry('blog', ['title' => 'Hello']);

    Queue::assertPushed(GenerateOgImage::class);
});

it('does not dispatch for collections that are not enabled', function () {
    enableCollections(['blog']);
    makeCollection('pages');

    makeEntry('pages', ['title' => 'About']);

    Queue::assertNotPushed(GenerateOgImage::class);
});

it('does not dispatch when auto-generation is disabled on the entry', function () {
    enableCollections(['blog']);
    makeCollection('blog');

    makeEntry('blog', ['title' => 'Hello', 'og_image_disabled' => true]);

    Queue::assertNotPushed(GenerateOgImage::class);
});

it('does not dispatch when the entry has a custom image', function () {
    enableCollections(['blog']);
    makeCollection('blog');

    makeEntry('blog', ['title' => 'Hello', 'og_image_custom' => 'photo.jpg']);

    Queue::assertNotPushed(GenerateOgImage::class);
});
