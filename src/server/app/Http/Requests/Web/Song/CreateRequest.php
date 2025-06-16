<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Song;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<string|Enum>>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
            ],
            'description' => [
                'required',
                'string',
            ],
            'song_type_id' => [
                'required',
                'string',
            ],
            'order_no' => [
                'required',
                'numeric',
            ],
            'lyricists.*.creator_id' => [
                'required',
                'string',
            ],
            'lyricists.*.order_no' => [
                'required',
                'numeric',
            ],
            'composers.*.creator_id' => [
                'required',
                'string',
            ],
            'composers.*.order_no' => [
                'required',
                'numeric',
            ],
            'arrangers.*.creator_id' => [
                'required',
                'string',
            ],
            'arrangers.*.order_no' => [
                'required',
                'numeric',
            ],
        ];
    }
}
