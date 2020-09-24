<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    const NAME_REGEX = '/^(([a-z0-9][\_]?)*([a-z0-9]))$/';
    const URL_REGEX = '/^([a-z0-9\-\.]*)$/';

    protected $fillable = [
        'title',
        'name',
        'url'
    ];

    public $timestamps = false;

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->using(ChannelTagPivot::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(ChannelUserPivot::class);
    }

    public function tagIds()
    {
        return $this->getAttribute('tags')->modelKeys();
    }
}
