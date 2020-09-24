<?php

namespace App;

use App\Enums\UserRole;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    const USERNAME_REGEX = '/^(([a-z0-9][\_]?)*([a-z0-9]))$/';

    use Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role'
    ];

    protected $hidden = [
        'email',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'permissions'
    ];

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function medium()
    {
        return $this->hasMany(Media::class);
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class)->using(ChannelUserPivot::class);
    }

    public function channelIds()
    {
        return $this->getAttribute('channels')->modelKeys();
    }

    public function isSuper()
    {
        return $this->role === UserRole::Superuser;
    }

    public function isEditor()
    {
        return $this->role === UserRole::Editor;
    }

    public function isContributor()
    {
        return $this->role === UserRole::Contributor;
    }

    public function isReadonly()
    {
        return $this->role === UserRole::Readonly;
    }

    public function isDisabled()
    {
        return $this->role === UserRole::Disabled;
    }
}
