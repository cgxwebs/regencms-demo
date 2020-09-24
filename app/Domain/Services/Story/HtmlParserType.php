<?php

namespace App\Domain\Services\Story;

use Mews\Purifier\Facades\Purifier;

class HtmlParserType implements ContentParserType
{
    public function parse($content)
    {
        return Purifier::clean($content);
    }
}
