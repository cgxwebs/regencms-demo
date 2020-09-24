<?php

namespace App\Http\Requests;

use App\Domain\Services\Channel\ChannelsTagsIndex;
use App\Enums\StoryFormat;
use App\Enums\StoryStatus;
use App\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StoryRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function prepareForValidation($autosave = [])
    {
        $this->merge([
            // For Blade simplicity, append _input as key name to display errors
            'tags_create_input' => $this->generateNewTags(),
            'body' => $this->mutateBody($this->body ?? []),
            'slug' => Str::lower($this->input('slug'))
        ]);
    }

    private function mutateBody(array $body)
    {
        $mut = [];
        $i = 1; // We renumber the keys for security
        $name_list = [];
        foreach ($body as $cont) {
            $name = $i > 1 ? Str::lower($cont['name'] ?? '') : 'default';
            $mut[$i] = [
                'name' => $name,
                'content' => $cont['content'] ?? '',
                'format' => $cont['format'] ?? ''
            ];
            $name_list[] = $name;
            $i++;
        }

        return $mut;
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
        $user = Auth::user();
        $rules = [
            'title' => 'present|max:120',
            'slug' => [
                'present',
                'alpha_dash',
                'max:120'
            ],
            'status' => [
                'required',
                Rule::in( StoryStatus::getValues() )
            ],

            // Content Data
            'body' => 'required|array|min:1',
            'body.*.name' => [
                'required',
                'alpha_dash'
            ],
            'body.*.content' => [
                'present'
            ],
            'body.*.format' => [
                'required',
                Rule::in( StoryFormat::getValues() )
            ],

            'tags' => [
                'array',
            ],
            'tags.*' => [
                'required',
                'integer',
                Rule::in( $tag_lister->getIdsAsArray() )
            ],

            'tags_create_input' => [
                'array',
            ],
            'tags_create_input.*' => [
                'regex:' . Tag::NAME_REGEX,
                'max:60',
                Rule::notIn( $tag_lister->getNamesAsArray() )
            ]
        ];

        if (!$user->isSuper()) {
            $rules['tags'][] = 'required';
            $rules['tags_create_input'][] = 'size:0';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateBodyNameUniqueness($validator->errors());
            $this->validateBodyJsonSyntax($validator->errors());
        });
    }

    private function validateBodyNameUniqueness($errors)
    {
        $names = [];
        foreach($this->body as $key => $cont) {
            if (in_array($cont['name'], $names)) {
                $errors->add("body.$key.name", 'The body.'.$key.'.name should be unique (default is reserved).');
            }
            $names[] = $cont['name'];
        }
    }

    private function validateBodyJsonSyntax($errors)
    {
        foreach($this->body as $key => $cont) {
            if ($cont['format'] == 'json') {
                $res = json_decode($cont['content']);
                if (is_null($res)) {
                    $errors->add("body.$key.name", 'The body.'.$key.'.name JSON syntax is invalid.');
                }
            }
        }
    }

    public function messages()
    {
        return [
            'tags_create_input.*.regex' => 'Tag can only contain alphanumeric, underscore and dot.',
            'tags_create_input.*.not_in' => 'Tag should be unique.',
            'tags.*.integer' => 'Invalid selected tag.',
            'tags.*.in' => 'Invalid selected tag.'
        ];
    }
}
