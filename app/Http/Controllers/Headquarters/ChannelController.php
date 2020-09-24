<?php

namespace App\Http\Controllers\Headquarters;

use App\Concerns\ModelTransaction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChannelRequest;
use App\Channel;
use App\Domain\Services\Channel\ChannelBuilder;
use App\Domain\Services\Channel\ChannelsTagsIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChannelController extends Controller
{
    use ModelTransaction;

    const ROUTE = 'hq.channels.';
    const VIEWDIR = 'hq/channel/';

    private ChannelsTagsIndex $ctIndex;

    public function __construct(
        ChannelsTagsIndex $ctIndex
    )
    {
        $this->ctIndex = $ctIndex;
    }

    public function index()
    {
        Gate::authorize('can-modify', 'channels');
        return view(self::VIEWDIR.'list', [
            'channels' => $this->ctIndex->getListWithRoutes('channel'),
            'action' => route(self::ROUTE.'store'),
            'edit' => null,
            'tags_list' => $this->ctIndex->getList(),
            'tags_default' => []
        ]);
    }

    public function edit(Channel $channel)
    {
        Gate::authorize('can-modify', 'channels');
        $this->authorize('write', $channel);
        return view(self::VIEWDIR.'list', [
            'channels' => $this->ctIndex->getListWithRoutes('channel'),
            'action' => route(self::ROUTE.'update', ['channel' => $channel]),
            'delete' => route(self::ROUTE.'delete', ['channel' => $channel]),
            'edit' => $channel,
            'tags_list' => $this->ctIndex->getList(),
            'tags_default' => $channel->tagIds()
        ]);
    }

    public function show(Channel $channel)
    {
        return redirect()->route(self::ROUTE.'edit', ['channel' => $channel]);
    }

    public function store(ChannelRequest $request)
    {
        Gate::authorize('is-superuser');
        $builder = new ChannelBuilder($request);
        $channel = $builder->build();

        return redirect()
               ->route(self::ROUTE.'edit', ['channel' => $channel])
               ->with('save_success', true);
    }

    public function update(ChannelRequest $request, Channel $channel)
    {
        Gate::authorize('can-modify', 'channels');
        $this->authorize('write', $channel);
        $builder = new ChannelBuilder($request);
        $channel = $builder->build($channel);

        return redirect()
               ->route(self::ROUTE.'edit', ['channel' => $channel])
               ->with('save_success', true);
    }

    public function delete(Channel $channel)
    {
        Gate::authorize('is-superuser');
        return view('layouts.dashboard_delete_item', [
            'action' => route(self::ROUTE . 'destroy', ['channel' => $channel]),
            'back_route' => route(self::ROUTE.'edit', ['channel' => $channel]),
            'item_type' => 'Channel',
            'item_name' => $channel->name,
        ]);
    }

    public function destroy(Request $request, Channel $channel)
    {
        Gate::authorize('is-superuser');
        $channel_name = $channel->name;

        $this->validate($request, [
            'password' => 'required|string|password'
        ]);

        if ($channel->exists) {
            $channel->delete();
        }

        return redirect()->route(self::ROUTE.'index')
            ->with('delete_success_message', sprintf("Channel '%s' has been deleted.", $channel_name));
    }

}
