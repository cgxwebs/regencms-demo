<?php

namespace App\Domain\Services\Story;

use App\Concerns\Assert;
use App\Enums\StoryFormat;
use App\Story;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final class StoryContent implements Arrayable, JsonSerializable
{
    private $content;
    private $format;
    private $name;

    public function __construct(...$args)
    {
        if (count($args) == 1) {
            $body = $args[0];
            $this->constructArray($body['name'], $body['content'], $body['format']);
        } else {
            $this->constructArray(...$args);
        }
    }

    private function constructArray($name, $content, $format)
    {
        Assert::oneOf($format, StoryFormat::getValues());
        $this->name = $name;
        $this->content = $content;
        $this->format = $format;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function withFilter(?Story $context = null)
    {
        return new FilteredContent($this->toArray(), $context);
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'content' => $this->getContent(),
            'format' => $this->getFormat(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
