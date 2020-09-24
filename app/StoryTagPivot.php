<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StoryTagPivot extends Pivot
{
    protected $table = 'story_tag';
}
