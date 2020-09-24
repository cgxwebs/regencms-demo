<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Media extends Model
{
    const FILENAME_REGEX = '/^(([a-z0-9][\-_]?)*([a-z0-9])(\.[a-z0-9]{1,5}))$/';

    protected $table = 'media';

    protected $fillable = [
        'filepath',
        'filetype',
        'filesize',
        'description',
        'user_id',
        'parent',
        'childtype'
    ];

    protected $with = [
        'user'
    ];

    protected $appends = [
        'created',
        'updated',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFilename()
    {
        return Str::of($this->filepath)->basename();
    }

    public function getCreatedAttribute()
    {
        $date = $this->created_at;
        $dt = [];
        $dt['human'] = $date->longRelativeToNowDiffForHumans();
        $dt['full'] = $date->toDateTimeString();
        $dt['pretty'] = $date->toDayDateTimeString();
        return $dt;
    }

    public function getUpdatedAttribute()
    {
        $date = $this->updated_at;
        $dt = [];
        $dt['human'] = $date->longRelativeToNowDiffForHumans();
        $dt['full'] = $date->toDateTimeString();
        $dt['pretty'] = $date->toDayDateTimeString();
        return $dt;
    }

    public function isImage()
    {
        return false !== strpos($this->filetype, 'image');
    }
}
