<?php

namespace App\Domain\Services\Channel;

use App\{Channel, Domain\TagRepository, Tag, User};
use App\Http\Controllers\Headquarters\ChannelController;
use App\Http\Controllers\Headquarters\TagController;
use Illuminate\Contracts\Auth\Guard;
use InvalidArgumentException;
use stdClass;

final class ChannelsTagsIndex
{

    private Guard $auth;

    private TagRepository $tagRepository;

    public function __construct(Guard $auth, TagRepository $tagRepository)
    {
        $this->auth = $auth;
        $this->tagRepository = $tagRepository;
    }

    public function getList(string $mode = 'tag'): array
    {
        $list = [];

        foreach ($this->getSource($mode) as $t) {
            $list[$t->id] = [
                'id' => $t->id,
                'name' => $t->name,
                'title' => $t->title
            ];
        }

        return $list;
    }

    public function getIdsAsArray(string $mode = 'tag')
    {
        $list = [];

        foreach ($this->getSource($mode) as $t) {
            $list[] = $t->id;
        }

        return $list;
    }

    public function getNamesAsArray(string $mode = 'tag')
    {
        $list = [];

        foreach ($this->getSource($mode) as $t) {
            $list[] = $t->name;
        }

        return $list;
    }

    public function getListWithRoutes(string $mode = 'tag')
    {
        $route_prefix = ($mode == 'tag') ? TagController::ROUTE : ChannelController::ROUTE;
        $list = [];

        foreach ($this->getSource($mode) as $t) {
            $item = new stdClass();
            $item->id = $t->id;
            $item->name = $t->name;
            $item->title = $t->title;
            $item->route = route($route_prefix.'edit', [$mode => $t]);

            $list[] = $item;
        }

        return $list;
    }

    public function getSource(string $mode)
    {
        if (!in_array($mode, ['tag', 'channel'])) {
            throw new InvalidArgumentException(self::class . ": invalid mode ($mode)");
        }

        return $this->retrieveIndex($mode);
    }

    private function retrieveIndex(string $mode)
    {
        $user = $this->auth->user();
        /**
         * @var $user User
         */
        if (is_null($user) || $user->isSuper()) {
            return $mode === 'channel' ?
                Channel::orderBy('name', 'asc')->get() :
                Tag::orderBy('name', 'asc')->get();
        }

        if ($user->isEditor()) {
            return $mode === 'channel' ?
                $user->channels :
                Tag::orderBy('name', 'asc')->get();
        }

        return $mode === 'channel' ?
            $user->channels :
            $this->tagRepository->getVisibleTagsByUser($user);
    }
}
