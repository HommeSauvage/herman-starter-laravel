<?php

use Inertia\Testing\AssertableInertia as Assert;

test('home page returns a successful response', function () {
    $this->get(route('home'))->assertOk();
});

test('home page renders the public home component with the app name', function () {
    config(['app.name' => 'Acme Starter']);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('public/home'));

    // The shared `name` prop and the blade <title> both carry the app name.
    $response->assertSee('<title>Acme Starter</title>', escape: false);
    $response->assertSee('Acme Starter');
});

test('home page contains no Laravel marketing or hardcoded brand links', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee('laravel.com');
});
