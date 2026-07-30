<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('unknown paths render the error page with a 404 status', function () {
    $response = $this->get('/definitely-not-a-page');

    $response->assertNotFound();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('error')
        ->where('status', 404),
    );
});

test('authenticated users get a 404 error page for missing team-scoped routes', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get("/{$user->currentTeam->slug}/notes/99999");

    $response->assertNotFound();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('error')
        ->where('status', 404),
    );
});

test('members of another team get a 403 error page', function () {
    $user = User::factory()->create();
    $outsider = User::factory()->create();

    $response = $this
        ->actingAs($outsider)
        ->get("/{$user->currentTeam->slug}/dashboard");

    $response->assertForbidden();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('error')
        ->where('status', 403),
    );
});

test('error responses keep JSON semantics for API-style requests', function () {
    $response = $this->getJson('/definitely-not-a-page');

    $response->assertNotFound();
    $response->assertHeader('content-type', 'application/json');
});
