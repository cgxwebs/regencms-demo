<?php

namespace App\Http\Requests;

use App\Domain\Services\Channel\ChannelsTagsIndex;
use App\Enums\UserRole;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $channel_lister = App::make(ChannelsTagsIndex::class);
        $rules = [
            'username' => ['required', 'string', 'min:4', 'max:60', 'regex:' . User::USERNAME_REGEX],
            'email' => ['required', 'string', 'email', 'max:100'],
            'role' => [ 'required', Rule::in(UserRole::getValues())],
            'channels' => 'array',
            'channels.*' => [
                'required',
                'integer',
                Rule::in($channel_lister->getIdsAsArray('channel'))
            ],
        ];

        if ($this->role !== UserRole::Superuser) {
            $rules['channels'] = 'required|array';
        }

        // Check uniqueness everytime we create
        if ($this->method() == 'POST') {
            $rules['username'][] = 'unique:users';
            $rules['email'][] = 'unique:users';
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        // Check uniqueness if name has been changed
        if ($this->method() == 'PUT') {
            $orig = $this->user;

            if (is_null($orig)) {
                abort(404);
            }

            if ($orig->username !== $this->username) {
                $rules['username'][] = 'unique:users';
            }

            if ($orig->email != $this->email) {
                $rules['email'][] = 'unique:users';
            }

            if ($this->change_password) {
                $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            }
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateRootConstraints($validator->errors());
        });
    }

    private function validateRootConstraints($errors)
    {
        // Check uniqueness if name has been changed
        if ($this->method() == 'PUT') {
            $orig = $this->user;

            if (is_null($orig)) {
                abort(404);
            }

            if ($orig->username !== config('regencms.root_username')) {
                return;
            }

            if ($orig->username != $this->username) {
                $errors->add("username", 'Root username cannot be modified.');
            }

            if ($this->role !== UserRole::Superuser) {
                $errors->add("role", 'Root role cannot be modified.');
            }
        }
    }

    public function messages()
    {
        return [
            'channels.*.integer' => 'Invalid selected channel.',
            'channels.*.in' => 'Invalid selected channel.'
        ];
    }
}
