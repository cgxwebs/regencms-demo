<?php

namespace App\Http\Requests;

use App\Media;
use Illuminate\Foundation\Http\FormRequest;

class MediaEditRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'filename' => 'required|max:40|regex:' . Media::FILENAME_REGEX,
            'description' => 'max:120'
        ];
    }

}
