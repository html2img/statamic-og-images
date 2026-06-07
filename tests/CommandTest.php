<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Statamic\Facades\Entry;

it('regenerates entries across enabled collections', function () {
    // Suppress the on-save listener so the only renders come from the command.
    Queue::fake();

    $history = [];
    fakeHttp([
        jsonResponse(200, ['success' => true, 'id' => 'a', 'url' => 'https://i.html2img.com/1.png']),
        jsonResponse(200, ['success' => true, 'id' => 'b', 'url' => 'https://i.html2img.com/2.png']),
    ], $history);

    enableCollections(['blog']);
    makeCollection('blog');
    $one = makeEntry('blog', ['title' => 'One']);
    $two = makeEntry('blog', ['title' => 'Two']);

    $this->artisan('og:generate')->assertSuccessful();

    expect($history)->toHaveCount(2)
        ->and(Entry::find($one->id())->value('og_image_url'))->not->toBeNull()
        ->and(Entry::find($two->id())->value('og_image_url'))->not->toBeNull();
});

it('can target a single collection', function () {
    Queue::fake();

    $history = [];
    fakeHttp([
        jsonResponse(200, ['success' => true, 'id' => 'a', 'url' => 'https://i.html2img.com/1.png']),
    ], $history);

    enableCollections(['blog']);
    makeCollection('blog');
    makeEntry('blog', ['title' => 'Only One']);

    $this->artisan('og:generate', ['--collection' => ['blog']])->assertSuccessful();

    expect($history)->toHaveCount(1);
});
