<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\Story\StoryReader;
use App\Domain\StoryPagerOptions;
use App\Domain\StoryRepository;
use App\Domain\TagRepository;
use App\Enums\TagVisibility;
use App\Http\Controllers\Controller;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoryApiController extends Controller
{
    use ApiConcerns;

    private StoryRepository $storyRepository;

    protected Request $request;

    private $page_limit = 10;

    private StoryReader $reader;

    private TagRepository $tagRepository;

    public function __construct(
        Request $request,
        StoryRepository $storyRepository,
        TagRepository $tagRepository,
        StoryReader $reader
    )
    {
        $this->request = $request;
        $this->storyRepository = $storyRepository;
        $this->tagRepository = $tagRepository;
        $this->reader = $reader;
    }

    public function getSingle(string $channel, string $story)
    {
        $channelModel = $this->findEntity('channel', $channel);
        $storyModel = $this->findEntity('story', $story);

        if ($storyModel) {
            $singleStory = $this->storyRepository->getSingleStory($channelModel->id, $storyModel->id);
            if ($singleStory) {
                $storyModel->setReadable($this->reader->read($storyModel));
                $response = $this->getApiResponse($storyModel);
            } else {
                $response = $this->getApiResponse([], 'Story not found.', 404);
            }
        }

        return $response;
    }

    public function listByChannel(string $channel)
    {
        $channelModel = $this->findEntity('channel', $channel);
        $pager = $this->getPagerAndValidate();
        $results = $this->storyRepository->getPublishedStories($channelModel->id, $pager);
        return $results;
    }

    public function listByTag(string $channel, string $tag)
    {
        $channelModel = $this->findEntity('channel', $channel);
        $tagModel = $this->findEntity('tag', $tag);
        /**
         * @var $tagModel Tag
         */
        if(!$tagModel->channels->contains($channelModel) || $tagModel->visibility === TagVisibility::Hidden) {
            return $this->getApiResponse([], "Tag not found.", 404);
        }
        $pager = $this->getPagerAndValidate();
        $results = $this->storyRepository->getPublishedStoriesByTag($channelModel->id, $tagModel->name, $pager);
        return $results;
    }

    private function getPagerAndValidate()
    {
        $default = [
            'page' => '1',
            'sort' => 'newest'
        ];

        $rules = [
            'page' => [
                'integer',
                'min:1'
            ],
            'sort' => [
                Rule::in(array_keys(StoryPagerOptions::SORT_OPTIONS))
            ],
        ];

        $query_input = array_merge(
            $default,
            $this->request->only('page', 'sort')
        );

        $this->getValidationFactory()->make(
            $query_input,
            $rules
        )->validate();

        return new StoryPagerOptions(
            config('regencms.api_perpage'),
            $query_input['page'],
            $query_input['sort']
        );
    }

}
