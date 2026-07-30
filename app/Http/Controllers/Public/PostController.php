<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

/**
 * REFERENCE MODULE — public read-side content controller for the Posts
 * quality-bar example. Delete this controller (and related Post files)
 * when Posts is unused.
 */
class PostController extends Controller
{
    /**
     * Display the paginated list of published posts.
     */
    public function index(): Response
    {
        $posts = Post::query()
            ->published()
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Post $post) => [
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'image_path' => $post->image_path,
                'published_at' => $post->published_at?->toIso8601String(),
            ]);

        return Inertia::render('public/posts/index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Display a single published post. Unpublished posts 404.
     */
    public function show(Post $post): Response
    {
        abort_unless($post->published_at !== null && $post->published_at->isPast(), 404);

        return Inertia::render('public/posts/show', [
            'post' => [
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'image_path' => $post->image_path,
                'published_at' => $post->published_at->toIso8601String(),
            ],
        ]);
    }
}
