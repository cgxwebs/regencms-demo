<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    const NAME_REGEX = '/^([a-z0-9\_]*[a-z0-9]\.)?([a-z0-9\_]*[a-z0-9])$/';

    protected $fillable = [
        'title',
        'name',
        'visibility'
    ];

    protected $hidden = [
        'pivot',
    ];

    public $timestamps = false;

    public function stories()
    {
        return $this->belongsToMany(Story::class)->using(StoryTagPivot::class);
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class)->using(ChannelTagPivot::class);
    }

}
