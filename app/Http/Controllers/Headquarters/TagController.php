<?php

namespace App\Http\Controllers\Headquarters;

use App\Concerns\ModelTransaction;
use App\Domain\Services\Channel\ChannelsTagsIndex;
use App\Enums\TagVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TagController extends Controller
{
    use ModelTransaction;

    const ROUTE = 'hq.tags.';
    const VIEWDIR = 'hq/tag/';

    private ChannelsTagsIndex $ctIndex;

    public function __construct(ChannelsTagsIndex $ctIndex)
    {
        $this->ctIndex = $ctIndex;
    }

    public function index()
    {
        Gate::authorize('can-modify', 'tags');
        return view(self::VIEWDIR.'list', [
            'tags' => $this->ctIndex->getListWithRoutes('tag'),
            'action' => route(self::ROUTE.'store'),
            'visibility_enum' => TagVisibility::getInstances(),
            'edit' => null,
        ]);
    }

    public function edit(Tag $tag)
    {
        Gate::authorize('can-modify', 'tags');
        return view(self::VIEWDIR.'list', [
            'tags' => $this->ctIndex->getListWithRoutes('tag'),
            'action' => route(self::ROUTE.'update', ['tag' => $tag]),
            'delete' => route(self::ROUTE.'delete', ['tag' => $tag]),
            'edit' => $tag,
            'visibility_enum' => TagVisibility::getInstances()
        ]);
    }

    public function show(Tag $tag)
    {
        return redirect()->route(self::ROUTE.'edit', ['tag' => $tag]);
    }

    public function store(TagRequest $request)
    {
        Gate::authorize('can-modify', 'tags');
        $tag = Tag::create($request->validated());

        return redirect()
               ->route(self::ROUTE.'edit', ['tag' => $tag])
               ->with('save_success', true);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        Gate::authorize('can-modify', 'tags');
        $tag->fill($request->validated())->save();
        return redirect()
               ->route(self::ROUTE.'edit', ['tag' => $tag])
               ->with('save_success', true);
    }

    public function delete(Tag $tag)
    {
        Gate::authorize('is-superuser');
        return view('layouts.dashboard_delete_item', [
            'action' => route(self::ROUTE . 'destroy', ['tag' => $tag]),
            'back_route' => route(self::ROUTE.'edit', ['tag' => $tag]),
            'item_type' => 'Tag',
            'item_name' => $tag->name,
        ]);
    }

    public function destroy(Request $request, Tag $tag)
    {
        Gate::authorize('is-superuser');
        $tag_name = $tag->name;

        $this->validate($request, [
            'password' => 'required|string|password'
        ]);

        if ($tag->exists) {
            $tag->delete();
        }

        return redirect()->route(self::ROUTE.'index')
            ->with('delete_success_message', sprintf("Tag '%s' has been deleted.", $tag_name));
    }
}
