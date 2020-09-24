<?php

namespace App\Domain\Services\Story;

use App\Story;

final class ContentFilter
{
    /**
     * @var $readable StoryContent|array
     */
    public function filter($readable, ?Story $context = null)
    {
        if (is_array($readable)) {
            return new FilteredContent($readable, $context);
        }
        return new FilteredContent($readable->toArray(), $context);
    }
}
