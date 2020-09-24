<?php

namespace App;

use App\Domain\Services\Story\StoryContent;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $fillable = [
        'title',
        'body',
        'status',
        'slug',
        'created_at',
        'updated_at',
        'user_id'
    ];

    protected $casts = [
        'body' => 'array',
    ];

    protected $appends = [
        'created',
        'updated',
        'readable'
    ];

    protected $hidden = [
        'body',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    protected $with = [
        'user',
        'tags'
    ];

    private $body_map;

    private array $readable = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->using(StoryTagPivot::class);
    }

    public function tagIds()
    {
        return $this->tags->modelKeys();
    }

    public function setReadable(array $readable): self
    {
        $this->readable = $readable;
        return $this;
    }

    public function getReadableAttribute()
    {
        return collect($this->readable);
    }

    /**
     * Transform body into an array of StoryContent objects
     */
    public function getBodyMap()
    {
        $this->extractBody();
        return $this->body_map;
    }

    /**
     * Converts body map objects as multi-dimensional array
     */
    public function getBodyMapAsArray()
    {
        $arr = [];
        foreach ($this->getBodyMap() as $content) {
            $arr[] = $content->toArray();
        }
        return $arr;
    }

    public function extractBody()
    {
        if (! $this->body_map || $this->isDirty('body') ) {
            $body = $this->body;
            ksort($body, SORT_NUMERIC);
            $this->body_map = [];
            foreach ($body as $b) {
                $content = new StoryContent($b);
                $this->body_map[$content->getName()] = $content;
            }
        }
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

    public function getCacheKey()
    {
        return 'story_' . $this->id;
    }

}
