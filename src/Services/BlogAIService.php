<?php

namespace Stumason12in12\Blog\Services;

use Illuminate\Support\Str;
use OpenAI;

class BlogAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('blog.open_ai_key'));
    }

    public function enhancePost(string $content): BlogAIEnhancement
    {
        $prompt = <<<EOT
You are a technical blog editor. Analyze the provided content and return a JSON object with EXACTLY these fields:
{
    "title": "A clear, engaging title (max 60 chars)",
    "category": "MUST be one of: Technology, Programming, Web Development, DevOps, Tutorial, Career, Best Practices, Software Engineering, Uncategorized",
    "excerpt": "An engaging summary (max 150 chars)",
    "enhanced_content": "The original content with improved markdown formatting"
}

Requirements:
- title: Must be descriptive and relevant to the content
- category: Must EXACTLY match one of the provided categories
- excerpt: Must be a coherent summary, not truncated mid-word
- enhanced_content: Must preserve all original content and meaning, only enhance markdown formatting

If you're unsure about any field, use these safe defaults:
- title: Use the most relevant phrase from the content
- category: "Uncategorized"
- excerpt: First 150 characters of content
- enhanced_content: Original content unchanged

Content to analyze:
{$content}
EOT;

        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
            ]);

            $result = json_decode($response->choices[0]->message->content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to parse OpenAI response as JSON');
            }

            // Validate category
            if (empty($result['category'])) {
                $result['category'] = 'Uncategorized';
            }

            // Ensure we have enhanced_content
            if (empty($result['enhanced_content'])) {
                $result['enhanced_content'] = $content;
            }

            // Ensure title exists and isn't too long
            if (empty($result['title'])) {
                $result['title'] = Str::limit(strip_tags($content), 60);
            }

            // Ensure excerpt exists and isn't too long
            if (empty($result['excerpt'])) {
                $result['excerpt'] = Str::limit(strip_tags($content), 150);
            }

            return new BlogAIEnhancement($result);

        } catch (\Exception $e) {
            // Log the error if you have logging configured
            \Log::error('AI Enhancement failed: '.$e->getMessage(), [
                'content_preview' => Str::limit($content, 100),
                'error' => $e->getMessage(),
            ]);

            // Return a safe default response
            return new BlogAIEnhancement([
                'title' => Str::limit(strip_tags($content), 60),
                'category' => 'Uncategorized',
                'excerpt' => Str::limit(strip_tags($content), 150),
                'enhanced_content' => $content,
            ]);
        }
    }
}
