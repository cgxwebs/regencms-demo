<?php

namespace App\Domain\Services\Story;

use GrahamCampbell\Markdown\Facades\Markdown;

final class MarkdownParserType extends HtmlParserType implements ContentParserType
{
    public function parse($content)
    {
        $markdown = Markdown::convertToHtml($content);
        return parent::parse($markdown);
    }
}
