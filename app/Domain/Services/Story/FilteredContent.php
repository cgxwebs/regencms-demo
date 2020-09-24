<?php


namespace App\Domain\Services\Story;


use App\Enums\StoryFormat;
use App\Story;

class FilteredContent
{
    private $content;
    private string $format;
    private string $name;
    private ?Story $context;

    public function __construct(array $content, ?Story $context = null)
    {
        $this->content = $content['content'];
        $this->format = $content['format'];
        $this->name = $content['name'];
        $this->context = $context;

        $this->useUnparsedJsonAsContent();
    }

    public function get()
    {
        return $this->content;
    }

    public function convertRelativeUrls(): self
    {
        if ($this->format != StoryFormat::Html && $this->format != StoryFormat::Markdown) {
            return $this;
        }

        $pattern = '/(\$url\()([a-zA-Z0-9\-\.\/\?#_&]{1,72})(\)\$)/';
        preg_match_all($pattern, $this->content, $matches);

        if (is_null($matches)) {
            return $this;
        }

        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $seek = $matches[0][$i];
            $url = $matches[2][$i];
            $this->content = str_replace($seek, url($url), $this->content);
        }

        return $this;
    }

    public function escapePlaintext(): self
    {
        if ($this->format != StoryFormat::Plaintext && $this->format != StoryFormat::Json) {
            return $this;
        }

        $this->content = htmlentities(
            $this->content,
            ENT_QUOTES | ENT_HTML401,
            'UTF-8',
            false
        );

        return $this;
    }

    public function convertLinebreaks(): self
    {
        if ($this->format != StoryFormat::Plaintext && $this->format != StoryFormat::Json) {
            return $this;
        }

        $this->conttent = nl2br($this->content);
        return $this;
    }

    private function useUnparsedJsonAsContent()
    {
        if ($this->format === StoryFormat::Json && !is_string($this->content)) {
            if (is_null($this->context)) {
                throw new \RuntimeException('Story context should be injected.');
            }
            $bodyMap = $this->context->getBodyMap();
            $this->content = $bodyMap[$this->name]->getContent();
        }
    }
}
