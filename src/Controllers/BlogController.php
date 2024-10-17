<?php

namespace Stumason12in12\Blog\Controllers;

use Stumason12in12\Blog\Models\BlogPost;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

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
        $files = Storage::disk('blog')->files();
        
        $processedCount = 0;
        
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            
            if ($extension === 'md' && !str_contains(strtolower($filename), 'draft')) {
                BlogPost::createFromFile($file);
                $processedCount++;
            }
        }
        
        return redirect()->route('blog.index')->with('message', "$processedCount blog posts synced successfully");
    }
}
