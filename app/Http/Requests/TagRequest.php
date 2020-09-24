<?php

namespace App\Http\Requests;

use App\Domain\Services\Channel\ChannelsTagsIndex;
use App\Enums\TagVisibility;
use App\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class TagRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'name' => Str::lower($this->name),
        ]);
    }

    public function rules()
    {
        $tag_lister = App::make(ChannelsTagsIndex::class);

        $rules = [
            'name' => [
                'required',
                'regex:' . Tag::NAME_REGEX,
                'max:60'
            ],
            'title' => 'present|max:120',
            'visibility' => [
                'required',
                Rule::in( TagVisibility::getValues() )
            ]
        ];

        // Check uniqueness everytime we create
        if ($this->method() == 'POST') {
            $rules['name'][] = Rule::notIn( $tag_lister->getNamesAsArray() );
        }

        // Check uniqueness if name has been changed
        if ($this->method() == 'PUT') {
            $orig_tag = $this->tag; // From router

            if (is_null($orig_tag)) {
                abort(404);
            }

            if ($orig_tag->name != $this->name) {
                $rules['name'][] = Rule::notIn( $tag_lister->getNamesAsArray() );
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.regex' => 'Tag should only contain alphanumeric, underscore and dot.',
            'name.not_in' => 'Tag should be unique.',
        ];
    }
}
