<?php

namespace Stumason12in12\Blog\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Stumason12in12\Blog\Models\BlogPost;

class BlogCommand extends Command
{
    public $signature = 'blog';

    public $description = 'My command';

    public function handle(): int
    {
        $this->sync();

        return self::SUCCESS;
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
    }
}
