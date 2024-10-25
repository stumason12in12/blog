<?php

namespace Stumason12in12\Blog\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;

class BlogAIEnhancement
{
    public string $title;

    public string $category;

    public string $excerpt;

    public string $enhanced_content;

    public function __construct(array $data)
    {
        // Validate required fields
        foreach (['title', 'category', 'excerpt', 'enhanced_content'] as $field) {
            if (! isset($data[$field]) || ! is_string($data[$field])) {
                throw new InvalidArgumentException("Missing or invalid field: {$field}");
            }
        }

        $this->title = $data['title'];
        $this->category = $data['category'];
        $this->excerpt = Str::limit($data['excerpt'], 150);
        $this->enhanced_content = $data['enhanced_content'];
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'excerpt' => $this->excerpt,
            'enhanced_content' => $this->enhanced_content,
        ];
    }
}
