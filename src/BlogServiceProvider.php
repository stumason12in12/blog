<?php

namespace Stumason12in12\Blog;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stumason12in12\Blog\Commands\BlogCommand;

class BlogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('blog')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_blog_table')
            ->hasCommand(BlogCommand::class);
    }
}
