<?php

namespace App\Domain\Services\Story;

final class PlaintextParserType implements ContentParserType
{
    public function parse($content)
    {
        return $content;
    }
}
