<?php

namespace App\Domain\Services\Channel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait TagSelectorBuilder
{
    private function toggleTags(?Model $model, Collection $validated)
    {
        $tags = $validated->get('tags');

        if ($tags) {
            $model->tags()->sync($tags);
        } else {
            $model->tags()->detach();
        }
    }

    private function saveNewTags(Model $model, Collection $validated, Collection $input)
    {
        $new_tags = $validated->get('tags_create_input');
        $has_supplied_new_tags = !empty($input->get('tags_create')) && count($new_tags) > 0;

        if ($has_supplied_new_tags) {
            $create_tags = [];
            foreach($new_tags as $n) {
                $create_tags[] = [
                    'name' => $n
                ];
            }
            $model->tags()->createMany($create_tags);
        }
    }
}
