<?php

namespace App\Http\Controllers\Headquarters;

use App\Domain\Services\Story\StoryReader;
use App\Http\Controllers\Controller;
use App\Domain\Services\Channel\ChannelsTagsIndex;
use App\Enums\StoryFormat;
use App\Enums\StoryStatus;
use App\Http\Requests\StoryRequest;
use App\Story;
use App\Domain\Services\Story\StoryBuilder;
use App\Domain\StoryRepository;
use App\Domain\StoryListFilter;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    const ROUTE = 'hq.stories.';
    const VIEWDIR = 'hq/story/';

    private StoryBuilder $storyBuilder;

    private StoryReader $storyReader;

    private StoryRepository $storyReadRepository;

    private Connection $db;

    private ChannelsTagsIndex $ctIndex;

    private Guard $auth;

    public function __construct(
        StoryBuilder $storyBuilder,
        StoryReader $storyReader,
        StoryRepository $storyReadRepository,
        Connection $db,
        ChannelsTagsIndex $ctIndex,
        Guard $auth
    )
    {
        $this->storyBuilder = $storyBuilder;
        $this->storyReader = $storyReader;
        $this->storyReadRepository = $storyReadRepository;
        $this->db = $db;
        $this->ctIndex = $ctIndex;
        $this->auth = $auth;
    }

    public function index(Request $request)
    {
        $auth = $this->auth->user();
        $filter = new StoryListFilter($this->db, $request, $auth, $this->ctIndex);

        $filter_selection = $filter->getFilterSelection();
        $filter->filterSelection($filter_selection);
        $filter->setRoute(route('hq.stories.index'));
        $filter->setPerPage(config('regencms.stories_perpage'));

        $story_paginator = $this->storyReadRepository->listStories($filter);
        $filter_pages = $filter->getPageUrls($story_paginator);
        $filter_options = $filter->getFilterOptions();

        $view_vars = [
            'stories' => $story_paginator->items(),
            'paginator' => $story_paginator,
            'defaults' => $filter_selection
        ];

        return view(self::VIEWDIR.'list', array_merge(
            $view_vars,
            $filter_options,
            $filter_pages
        ));
    }

    public function show(Story $story)
    {
        $this->authorize('read', $story);
        /**
         * @var $fullStory Story
         */
        $fullStory = Story::with('tags.channels')
            ->where(['id' => $story->id])
            ->first();

        $fullStory->setReadable($this->storyReader->read($story));

        return view(self::VIEWDIR.'single', [
            'story' => $fullStory
        ]);
    }

    public function create()
    {
        $this->authorize('create', Story::class);
        return view(self::VIEWDIR.'form', [
            'action' => route(self::ROUTE.'store'),
            'author' => $this->auth->user(),
            'edit' => null,
            'status_enum' => StoryStatus::getInstances(),
            'format_enum' => StoryFormat::getInstances(),
            'tags_list' => $this->ctIndex->getList(),
            'tags_default' => []
        ]);
    }

    public function store(StoryRequest $request)
    {
        $this->authorize('create', Story::class);
        $user = $request->user();
        $story = $this->storyBuilder->build($request, $user);

        return redirect()
               ->route(self::ROUTE.'edit', ['story' => $story])
               ->with('save_success', true)
               ->with('save_view_url', route(self::ROUTE.'show', $story));
    }

    public function edit(Story $story)
    {
        $hasPerm = $this->auth->user()->can('write', $story);
        if (!$hasPerm) {
            return redirect()->route(self::ROUTE.'show', [
                'story' => $story
            ]);
        }

        return view(self::VIEWDIR.'form', [
            'action' => route(self::ROUTE.'update', ['story' => $story]),
            'author' => $this->auth->user(),
            'edit' => $story,
            'delete' => route(self::ROUTE . 'delete', ['story' => $story]),
            'status_enum' => StoryStatus::getInstances(),
            'format_enum' => StoryFormat::getInstances(),
            'tags_list' => $this->ctIndex->getList(),
            'tags_default' => $story->tagIds()
        ]);
    }

    public function update(StoryRequest $request, Story $story)
    {
        $this->authorize('write', $story);
        $user = $request->user();
        $story = $this->storyBuilder->build($request, $user, $story);

        return redirect()
               ->route(self::ROUTE.'edit', ['story' => $story])
               ->with('save_success', true)
               ->with('save_view_url', route(self::ROUTE.'show', $story));
    }

    public function delete(Story $story)
    {
        $this->authorize('write', $story);
        return view('layouts.dashboard_delete_item', [
            'action' => route(self::ROUTE . 'destroy', ['story' => $story]),
            'back_route' => route(self::ROUTE.'edit', ['story' => $story]),
            'item_type' => 'Story',
            'item_name' => $story->title ?? '(untitled)',
        ]);
    }

    public function destroy(Request $request, Story $story)
    {
        $this->authorize('write', $story);
        $story_title = $story->title ?? '(untitled)';

        $this->validate($request, [
            'password' => 'required|string|password'
        ]);

        if ($story->exists) {
            $story->delete();
        }

        return redirect()->route(self::ROUTE . 'index')
            ->with('delete_success_message', sprintf("Story '%s' has been deleted.", $story_title));
    }
}
