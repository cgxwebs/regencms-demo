<?php

namespace App\Domain\Services\Channel;

use App\Http\Requests\ChannelRequest;
use App\{Channel};
use App\Concerns\ModelTransaction;
use Illuminate\Support\Collection;

final class ChannelBuilder
{
    use ModelTransaction, TagSelectorBuilder;

    private Collection $input;
    private Collection $validated;
    private $channel;

    public function __construct(ChannelRequest $request)
    {
        $this->input = collect($request->all());
        $this->validated = collect($request->validated());
    }

    public function build(?Channel $channel = null)
    {
        $c = is_object($channel) ? clone $channel : null;

        $this->performTransaction('saveChannel', $c);
        return $this->channel;
    }

    private function saveChannel(?Channel $channel = null)
    {
        $this->channel = $channel;

        $this->buildModel();

        $this->toggleTags($this->channel, $this->validated);
        $this->saveNewTags($this->channel, $this->validated, $this->input);
    }

    private function buildModel()
    {
        $props = $this->validated->toArray();

        if (is_null($this->channel)) {
            $this->channel = Channel::create($props);
        } else {
            $this->channel->fill($props)->save();
        }
    }
}
