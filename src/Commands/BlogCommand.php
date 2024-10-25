<?php

namespace Stumason12in12\Blog\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Stumason12in12\Blog\Models\BlogPost;

class BlogCommand extends Command
{
    public $signature = '12in12:blog';

    public $description = 'Sync markdown files to blog posts';

    public function handle(): int
    {
        $this->info('Starting blog sync...');

        $disk = config('blog.storage_disk', 'blog');
        $this->info("Using storage disk: {$disk}");

        // Check if the disk exists
        if (! Storage::disk($disk)->exists('')) {
            $this->error("Storage disk '{$disk}' is not properly configured!");

            return self::FAILURE;
        }

        $files = Storage::disk($disk)->files();
        $this->info('Found '.count($files).' files');

        if (empty($files)) {
            $this->warn('No files found in the blog storage disk. Path: '.Storage::disk($disk)->path(''));

            return self::FAILURE;
        }

        $processedCount = 0;

        foreach ($files as $file) {
            $this->info("Processing file: {$file}");

            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if ($extension === 'md' && ! str_contains(strtolower($filename), 'draft')) {
                try {
                    BlogPost::createFromFile($file);
                    $processedCount++;
                    $this->info("Successfully processed: {$file}");
                } catch (\Exception $e) {
                    $this->error("Failed to process {$file}: ".$e->getMessage());
                }
            } else {
                $this->line("Skipping {$file} - not a markdown file or is a draft");
            }
        }

        $this->info("Processed {$processedCount} posts");

        return self::SUCCESS;
    }
}
