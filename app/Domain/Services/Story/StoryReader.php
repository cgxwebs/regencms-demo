<?php


namespace App\Domain\Services\Story;


use App\Story;
use Illuminate\Cache\Repository;

class StoryReader
{
    private Repository $cache;
    private ContentParser $parser;

    public function __construct(Repository $cache, ContentParser $parser)
    {
        $this->cache = $cache;
        $this->parser = $parser;
    }

    public function read(Story $story)
    {
        return $this->cache->rememberForever($story->getCacheKey(), function() use ($story) {
            $body = $story->getBodyMap();
            $readableCollection = [];
            /**
             * @var $item StoryContent
             */
            foreach($body as $storyContent) {
                $readable = $storyContent->toArray();
                $readable['content'] = $this->getParsed($storyContent);
                $readableCollection[$readable['name']] = new StoryContent($readable);
            }
            return $readableCollection;
        });
    }

    public function getParsed(StoryContent $storyContent)
    {
        if(!$this->parser->isEligible($storyContent->getFormat())) {
            return $storyContent->getContent();
        }

        return $this->parser->parse(
            $storyContent->getContent(),
            $storyContent->getFormat()
        );
    }

}
