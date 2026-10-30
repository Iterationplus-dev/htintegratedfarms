<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('pages.news.index', [
            'posts' => Post::query()->published()->with('media')->latest('published_at')->paginate(9),
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->isPublished(), 404);

        return view('pages.news.show', [
            'post' => $post->loadMissing('media'),
            'more' => Post::query()->published()->whereKeyNot($post->getKey())->with('media')->latest('published_at')->limit(3)->get(),
        ]);
    }
}
