<?php

namespace Stumason12in12\Blog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
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

    public function sync()
    {
        $files = Storage::disk(config('blog.storage_disk'))->files();

        $processedCount = 0;

        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if ($extension === 'md' && ! str_contains(strtolower($filename), 'draft')) {
                BlogPost::createFromFile($file);
                $processedCount++;
            }
        }

        return redirect()->route('blog.index')->with('message', "$processedCount blog posts synced successfully");
    }
}
