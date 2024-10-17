<?php

namespace Stumason12in12\Blog\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Stumason12in12\Blog\Blog
 */
class Blog extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Stumason12in12\Blog\Blog::class;
    }
}
