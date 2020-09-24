<?php

namespace App\Domain\Services\Story;

use App\Http\Requests\StoryRequest;
use Illuminate\Cache\Repository;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Throwable;
use App\{User, Story};
use Illuminate\Support\Collection;
use InvalidArgumentException;
use App\Domain\Services\Channel\TagSelectorBuilder;

final class StoryBuilder
{
    use TagSelectorBuilder;

    private Collection $input;
    private Collection $validated;
    private $user;
    private $story;

    private Connection $db;

    private Repository $cache;

    public function __construct(Connection $db, Repository $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    public function build(StoryRequest $request, User $user, ?Story $story = null): Model
    {
        if (false == $user->exists) {
            throw new InvalidArgumentException('User does not exists');
        }

        $input = collect($request->all());
        $validated = collect($request->validated());

        $this->db->beginTransaction();
        try {
            $story = $this->buildModel(
                $this->resortBodyIndex($validated),
                $user,
                $story
            );
            $this->toggleTags($story, $validated);
            $this->saveNewTags($story, $validated, $input);
            $this->db->commit();
        } catch (Throwable $t) {
            $this->db->rollBack();
            throw $t;
        }

        return $story;
    }

    private function buildModel(array $props, User $user, ?Story $story): Model
    {
        if (is_null($story)) {
            $story = $user->stories()->create($props);
        } else {
            $story->update($props);
        }

        $this->cache->forget($story->getCacheKey());

        return $story;
    }

    private function resortBodyIndex(Collection $validated): array
    {
        $data = clone $validated;

        $sorted = [];
        // Enforce hashed numeric indexes
        $i = 0;
        foreach($data['body'] as $b) {
            $sorted[sprintf('#%03d', $i)] = $b;
            $i++;
        }

        $data->put('body', $sorted);
        return $data->toArray();
    }

}
