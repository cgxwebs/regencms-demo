<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChannelUserPivot extends Pivot
{
    protected $table = 'channel_user';

    protected $fillable = ['user_id', 'channel_id'];

    public $timestamps = false;
}
