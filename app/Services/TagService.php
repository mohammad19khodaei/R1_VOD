<?php

namespace App\Services;

use App\Article;
use App\Tag;

class TagService
{
    public function addArticleTags(Article $article, array $inputTags = []): void
    {
        if (empty($inputTags)) {
            return;
        }

        $tags = array_map(fn($name) => Tag::firstOrCreate(['name' => $name])->id, $inputTags);

        $article->tags()->attach($tags);
    }
}