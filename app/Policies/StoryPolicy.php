<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Story;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Connection;

class StoryPolicy
{
    use HandlesAuthorization;

    /**
     * @var Connection
     */
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function create(User $user)
    {
        if (!$user->isReadonly() && !$user->isDisabled()) {
            return true;
        }
        return false;
    }

    public function write(User $user, Story $story)
    {
        if ($user->isSuper() || $user->isEditor()) {
            return true;
        }

        if ($user->isReadonly()) {
            return false;
        }

        if ($story->tags->count() > 0 && count($user->channels)) {
            if ($user->id === $story->user->id && $this->getDisjointCount($user, $story) === 0) {
                return true;
            }
        }

        return false;
    }

    public function read(User $user, Story $story)
    {
        if ($user->isSuper() || $user->isEditor()) {
            return true;
        }

        if ($story->tags->count() > 0 && count($user->channels)) {;
            if ($this->getDisjointCount($user, $story) === 0) {
                return true;
            }
        }

        return false;
    }

    private function getDisjointCount(User $user, Story $story)
    {
        $disjoint = $this->db->select(
            "select count(*) as disjoint from story_tag st
            where st.story_id = :story_id
            and not exists (
                select ct.tag_id from channel_tag ct
                inner join channel_user cu on ct.channel_id = cu.channel_id
                where cu.user_id = :user_id
                and st.tag_id = ct.tag_id
            )",
            [ 'user_id' => $user->id, 'story_id' => $story->id ]
        );

        return $disjoint[0]->disjoint;
    }
}
