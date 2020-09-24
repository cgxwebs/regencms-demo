<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Media;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Connection;

class MediaPolicy
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

    public function write(User $user, Media $media)
    {
        if ($user->isSuper()) {
            return true;
        }

        if ($user->isEditor() && !$media->user->isSuper()) {
            return true;
        }

        if (!$user->isReadonly() && !$user->isDisabled()) {
            if ($media->user_id === $user->id) {
                return true;
            }
        }

        return Response::deny('You are not authorized to modify this file.');
    }

    public function read(User $user)
    {
        if (!$user->isReadonly() && !$user->isDisabled()) {
            return true;
        }
        return false;
    }
}
