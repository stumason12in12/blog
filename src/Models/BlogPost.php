<?php

namespace Stumason12in12\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelMarkdown\MarkdownRenderer;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'content', 'author', 'category', 'excerpt', 'reading_time'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['rendered_content'];

    public static function createFromFile($filename)
    {
        $content = Storage::disk('blog')->get($filename);
        $title = pathinfo($filename, PATHINFO_FILENAME);
        $slug = \Str::slug($title);

        $excerpt = \Str::limit(strip_tags($content), 150);
        $reading_time = ceil(str_word_count(strip_tags($content)) / 200);

        return static::create([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'author' => 'Stu Mason',
            'category' => 'Uncategorized',
            'excerpt' => $excerpt,
            'reading_time' => $reading_time,
        ]);
    }

    public function getRenderedContentAttribute()
    {
        return app(MarkdownRenderer::class)->toHtml($this->content);
    }
}