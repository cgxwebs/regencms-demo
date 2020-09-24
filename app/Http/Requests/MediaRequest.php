<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $filesize = ceil(floatval(config('regencms.media_filesize')) * 1024);

        return [
            'media' => 'required',
            'media.*' => [
                'file',
                'mimetypes:' . implode(',', config('regencms.media_mimetypes')),
                'max:' .  $filesize
            ]
        ];
    }

    public function messages()
    {
        return [
            'media.*.mimetypes' => sprintf('Some file types were not accepted.'),
            'media.*.max' => sprintf('Some file exceeded max file size of %d MB.', config('regencms.media_filesize')),
        ];
    }
}
