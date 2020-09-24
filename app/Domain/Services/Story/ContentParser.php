<?php


namespace App\Domain\Services\Story;


use InvalidArgumentException;

class ContentParser
{

    private HtmlParserType $htmlParser;

    private MarkdownParserType $markdownParser;

    private JsonParserType $jsonParser;


    public function __construct(
        HtmlParserType $htmlParser,
        MarkdownParserType $markdownParser,
        JsonParserType $jsonParserType
    ) {
        $this->htmlParser = $htmlParser;
        $this->markdownParser = $markdownParser;
        $this->jsonParser = $jsonParserType;
    }

    public function parse(string $content, string $format)
    {
        if (!$this->isEligible($format)) {
            throw new InvalidArgumentException("Invalid parser format");
        }

        $parserType = $this->getParserType($format);
        return $this->$parserType->parse($content);
    }

    public function isEligible(string $format)
    {
        $parserType = $this->getParserType($format);
        return isset($this->$parserType);
    }

    public function getParserType(string $format)
    {
        return strtolower($format).'Parser';
    }
}
