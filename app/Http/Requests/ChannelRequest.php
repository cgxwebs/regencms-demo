<?php

namespace App\Http\Requests;

use App\{Channel,Tag};
use App\Domain\Services\Channel\ChannelsTagsIndex;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ChannelRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'name' => Str::lower($this->name),
            'tags_create_input' => $this->generateNewTags()
        ]);
    }

    private function generateNewTags(): array
    {
        $input = $this->tags_create;

        if (false == is_string($input)) {
            return [];
        }

        return array_map('trim', explode(',', Str::lower($input)));
    }

    public function rules()
    {
        $tag_lister = App::make(ChannelsTagsIndex::class);

        $rules = [
            'name' => [
                'required',
                'regex:' . Channel::NAME_REGEX,
                'max:60'
            ],
            'title' => 'present|max:120',
            'url' => 'required|'.'regex:' . Channel::URL_REGEX,
            'tags' => 'required|array',
            'tags.*' => [
                'integer',
                Rule::in( $tag_lister->getIdsAsArray() )
            ],
            'tags_create_input' => 'array',
            'tags_create_input.*' => [
                'regex:' . Tag::NAME_REGEX,
                'max:60',
                Rule::notIn( $tag_lister->getNamesAsArray() )
            ]
        ];

        //Check uniqueness everytime we create
        if ($this->method() == 'POST') {
            $rules['name'][] = 'unique:channels';
        }

        // Check uniqueness if name has been changed
        if ($this->method() == 'PUT') {
            $orig = $this->channel;

            if (is_null($orig)) {
                abort(404);
            }

            if ($orig->name != $this->name) {
                $rules['name'][] = 'unique:channels';
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.regex' => 'Channel should only contain alphanumeric and underscore.',
            'tags_create_input.*.regex' => 'Tag can only contain alphanumeric, underscore and dot.',
            'tags_create_input.*.not_in' => 'Tag should be unique.',
            'tags.*.integer' => 'Invalid selected tag.',
            'tags.*.in' => 'Invalid selected tag.',
        ];
    }
}
