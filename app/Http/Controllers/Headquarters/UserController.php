<?php

namespace App\Http\Controllers\Headquarters;

use App\Domain\Services\Channel\ChannelsTagsIndex;
use App\Domain\Services\User\UserMigrator;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\User;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    const ROUTE = 'hq.users.';
    const VIEWDIR = 'hq/user/';

    private Connection $db;
    private ChannelsTagsIndex $ctIndex;

    public function __construct(Connection $db, ChannelsTagsIndex $ctIndex)
    {
        $this->db = $db;
        $this->ctIndex = $ctIndex;
    }

    public function index()
    {
        Gate::authorize('is-superuser');
        $users = User::orderBy('username', 'asc')->get();
        return view(self::VIEWDIR . 'list', [
            'users' => $users,
            'action' => route(self::ROUTE . 'store'),
            'role_enum' => UserRole::getInstances(),
            'edit' => null,
            'channels_list' => $this->ctIndex->getList('channel'),
            'channels_default' => []
        ]);
    }

    public function edit(User $user)
    {
        Gate::authorize('is-superuser');
        $users = User::orderBy('username', 'asc')->get();
        return view(self::VIEWDIR . 'list', [
            'users' => $users,
            'action' => route(self::ROUTE . 'update', ['user' => $user]),
            'role_enum' => UserRole::getInstances(),
            'edit' => $user,
            'delete' => route(self::ROUTE.'delete', ['user' => $user]),
            'channels_list' => $this->ctIndex->getList('channel'),
            'channels_default' => $user->channelIds()
        ]);
    }

    public function store(UserRequest $request)
    {
        Gate::authorize('is-superuser');
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $channels = $data['channels'] ?? [];
        if ($channels) {
            $user->channels()->sync($channels);
        } else {
            $user->channels()->detach();
        }
        return redirect()
               ->route(self::ROUTE . 'edit', ['user' => $user])
               ->with('save_success', true);
    }

    public function update(UserRequest $request, User $user)
    {
        Gate::authorize('is-superuser');
        $data = $request->validated();
        if ($request->change_password) {
            $data['password'] = Hash::make($data['password']);
        } else {
            if (isset($data['password'])) {
                unset($data['password']);
            }
        }
        $user->update($data);

        $channels = $data['channels'] ?? [];
        if ($channels) {
            $user->channels()->sync($channels);
        } else {
            $user->channels()->detach();
        }

        return redirect()
               ->route(self::ROUTE . 'edit', ['user' => $user])
               ->with('save_success', true);
    }

    public function delete(User $user)
    {
        Gate::authorize('is-superuser');
        return view('layouts.dashboard_delete_item', [
            'action' => route(self::ROUTE . 'destroy', ['user' => $user]),
            'back_route' => route(self::ROUTE.'edit', ['user' => $user]),
            'item_type' => 'User',
            'item_name' => $user->username,
        ]);
    }

    public function destroy(Request $request, UserMigrator $migrator, User $user)
    {
        Gate::authorize('is-superuser');
        $user_name = $user->username;

        $this->validate($request, [
            'password' => 'required|string|password'
        ]);

        if (strtolower($user->username) === config('regencms.root_username'))
        {
            return redirect()->route(self::ROUTE.'index')
                ->with('delete_error_message', sprintf("User '%s' cannot be deleted.", $user_name));
        }

        if (!$user->exists) {
            return redirect()->route(self::ROUTE.'index')
                ->with('delete_error_message', sprintf("User '%s' is non-existent.", $user_name));
        }

        $migrator->transferStoriesToRoot($user);
        $migrator->transferMediaToRoot($user);

        $user->delete();

        return redirect()->route(self::ROUTE.'index')
            ->with('delete_success_message', sprintf("User '%s' has been deleted.", $user_name));
    }

}
