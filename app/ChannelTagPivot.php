<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChannelTagPivot extends Pivot
{
    protected $table = 'channel_tag';

    protected $fillable = ['tag_id', 'channel_id'];

    public $timestamps = false;
}
