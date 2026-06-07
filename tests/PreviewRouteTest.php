<?php

declare(strict_types=1);

use Statamic\Facades\User;

it('is gated to authenticated control panel users', function () {
    $this->get('/cp/og-images/preview')->assertRedirect();
});

it('renders the sample template for an authenticated user', function () {
    $user = User::make()->email('admin@example.com')->makeSuper()->save();

    $response = $this->actingAs($user)->get('/cp/og-images/preview');

    $response->assertOk();
    expect($response->getContent())->toContain('How real Chrome rendering changes social images');
});

it('renders a specific entry when given its slug', function () {
    $user = User::make()->email('admin@example.com')->makeSuper()->save();
    makeCollection('blog');
    makeEntry('blog', ['title' => 'A Very Specific Headline', 'slug' => 'specific']);

    $response = $this->actingAs($user)->get('/cp/og-images/preview?entry=specific');

    $response->assertOk();
    expect($response->getContent())->toContain('A Very Specific Headline');
});
