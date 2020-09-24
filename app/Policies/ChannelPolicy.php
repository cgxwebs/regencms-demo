<?php

namespace App\Policies;

use App\Channel;
use App\Story;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Connection;

class ChannelPolicy
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

    public function write(User $user, Channel $channel)
    {
        if ($user->isSuper()) {
            return true;
        }

        if ($user->isEditor()) {
            if ($user->channels->contains($channel)) {
                return true;
            }
        }

        return Response::deny('You do not have access to this channel.');
    }

    public function read(User $user, Story $story)
    {
        if ($user->isSuper()) {
            return true;
        }

        if ($user->isEditor()) {
            if ($user->channels->contains($channel)) {
                return true;
            }
        }

        return Response::deny('You do not have access to this channel.');
    }

    public function delete(User $user)
    {
        if ($user->isSuper()) {
            return true;
        }

        return Response::deny('Only super administrators can delete channels.');
    }
}
