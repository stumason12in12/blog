<?php

namespace Stumason12in12\Blog\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Stumason12in12\Blog\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::latest()->get();

        return Inertia::render('Blog/Index', ['posts' => $posts]);
    }

    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();

        return Inertia::render('Blog/Show', ['post' => $post]);
    }
}
