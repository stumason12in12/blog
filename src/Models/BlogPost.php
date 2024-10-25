<?php

namespace Stumason12in12\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelMarkdown\MarkdownRenderer;
use Stumason12in12\Blog\Services\BlogAIService;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'author',
        'category',
        'excerpt',
        'reading_time',
        'ai_processed',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'ai_processed' => 'boolean',
    ];

    protected $appends = ['rendered_content'];

    /**
     * Create or update a blog post from a markdown file
     */
    public static function createFromFile(string $filename): static
    {
        try {
            $content = Storage::disk('blog')->get($filename);
            $originalTitle = pathinfo($filename, PATHINFO_FILENAME);
            $slug = Str::slug($originalTitle);

            $existingPost = static::where('slug', $slug)->first();

            if ($existingPost) {
                return static::updateExistingPost($existingPost, $content);
            }

            return static::createNewPost($slug, $content);
        } catch (\Exception $e) {
            Log::error('Failed to create/update blog post', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing blog post
     *
     * @param  static  $post
     */
    protected static function updateExistingPost(self $post, string $content): static
    {
        // If not AI processed yet, enhance it
        if (! $post->ai_processed) {
            try {
                $aiService = app(BlogAIService::class);
                $enhanced = $aiService->enhancePost($content)->toArray();

                $post->update([
                    'title' => $enhanced['title'],
                    'content' => $enhanced['enhanced_content'],
                    'category' => $enhanced['category'],
                    'excerpt' => $enhanced['excerpt'],
                    'reading_time' => static::calculateReadingTime($enhanced['enhanced_content']),
                    'ai_processed' => true,
                ]);
            } catch (\Exception $e) {
                Log::warning('AI processing failed, updating with original content', [
                    'post_id' => $post->id,
                    'error' => $e->getMessage(),
                ]);

                // Fallback to basic update if AI processing fails
                $post->update([
                    'content' => $content,
                    'reading_time' => static::calculateReadingTime($content),
                ]);
            }

            return $post;
        }

        // If already AI processed, just update content
        $post->update([
            'content' => $content,
            'reading_time' => static::calculateReadingTime($content),
        ]);

        return $post;
    }

    /**
     * Create a new blog post
     */
    protected static function createNewPost(string $slug, string $content): static
    {
        try {
            $aiService = app(BlogAIService::class);
            $enhanced = $aiService->enhancePost($content)->toArray();

            return static::create([
                'title' => $enhanced['title'],
                'slug' => $slug,
                'content' => $enhanced['enhanced_content'],
                'author' => config('blog.author', 'Stu Mason'),
                'category' => $enhanced['category'],
                'excerpt' => $enhanced['excerpt'],
                'reading_time' => static::calculateReadingTime($enhanced['enhanced_content']),
                'ai_processed' => true,
            ]);
        } catch (\Exception $e) {
            Log::warning('AI processing failed for new post, creating with defaults', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);

            // Fallback to creating post without AI enhancement
            return static::create([
                'title' => Str::title(str_replace('-', ' ', $slug)),
                'slug' => $slug,
                'content' => $content,
                'author' => config('blog.author', 'Stu Mason'),
                'category' => 'Uncategorized',
                'excerpt' => Str::limit(strip_tags($content), 150),
                'reading_time' => static::calculateReadingTime($content),
                'ai_processed' => false,
            ]);
        }
    }

    /**
     * Calculate reading time in minutes
     */
    protected static function calculateReadingTime(string $content): int
    {
        return max(1, ceil(str_word_count(strip_tags($content)) / 200));
    }

    /**
     * Get rendered HTML content
     */
    public function getRenderedContentAttribute(): string
    {
        return app(MarkdownRenderer::class)->toHtml($this->content);
    }
}
