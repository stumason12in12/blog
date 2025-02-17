<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Stumason12in12\Blog\BlogPost;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition()
    {
        $title = $this->faker->sentence;

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraphs(3, true),
            'author' => $this->faker->name,
            'category' => $this->faker->word,
            'excerpt' => $this->faker->sentence,
            'reading_time' => $this->faker->numberBetween(1, 10),
            'ai_processed' => $this->faker->boolean,
        ];
    }
}
