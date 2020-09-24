<?php

namespace App\Domain\Services\Story;

final class JsonParserType implements ContentParserType
{
    public function parse($content)
    {
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        return is_null($decoded) ? [] : $decoded;
    }
}
