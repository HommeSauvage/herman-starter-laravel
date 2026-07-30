<?php

use App\Models\Post;
use Inertia\Testing\AssertableInertia as Assert;

test('posts index returns a successful response', function () {
    $this->get(route('posts.index'))->assertOk();
});

test('posts index lists published posts newest first', function () {
    Post::factory()->create([
        'title' => 'Older post',
        'published_at' => now()->subWeeks(2),
    ]);
    Post::factory()->create([
        'title' => 'Newer post',
        'published_at' => now()->subWeek(),
    ]);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/posts/index')
            ->has('posts.data', 2)
            ->where('posts.data.0.title', 'Newer post')
            ->where('posts.data.1.title', 'Older post'),
        );
});

test('posts index hides drafts and scheduled posts', function () {
    Post::factory()->create(['title' => 'Published']);
    Post::factory()->draft()->create(['title' => 'Draft']);
    Post::factory()->scheduled()->create(['title' => 'Scheduled']);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/posts/index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Published'),
        );
});

test('posts index paginates beyond nine posts', function () {
    Post::factory()->count(10)->create();

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/posts/index')
            ->has('posts.data', 9)
            ->where('posts.last_page', 2),
        );

    $this->get(route('posts.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/posts/index')
            ->has('posts.data', 1)
            ->where('posts.current_page', 2),
        );
});

test('published posts are shown by slug', function () {
    $post = Post::factory()->create([
        'title' => 'Token driven design',
        'slug' => 'token-driven-design',
    ]);

    $this->get(route('posts.show', ['post' => $post->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/posts/show')
            ->where('post.title', 'Token driven design')
            ->where('post.slug', 'token-driven-design')
            ->has('post.body')
            ->has('post.excerpt'),
        );
});

test('slugs are derived from the title when omitted', function () {
    $post = Post::factory()->create(['title' => 'Hello Starter World']);

    expect($post->slug)->toBe('hello-starter-world');
});

test('unpublished posts render the 404 error page', function () {
    $draft = Post::factory()->draft()->create();

    $response = $this->get(route('posts.show', ['post' => $draft->slug]));

    $response->assertNotFound();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('error')
        ->where('status', 404),
    );
});

test('scheduled posts render the 404 error page', function () {
    $scheduled = Post::factory()->scheduled()->create();

    $response = $this->get(route('posts.show', ['post' => $scheduled->slug]));

    $response->assertNotFound();
});

test('unknown post slugs render the 404 error page', function () {
    $response = $this->get(route('posts.show', ['post' => 'no-such-post']));

    $response->assertNotFound();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('error')
        ->where('status', 404),
    );
});
