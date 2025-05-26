<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\SongType;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'song_type_id' => [
                'required',
            ],
            'song_type_name' => [
                'required',
                'string',
            ],
            'order_no' => [
                'required',
                'numeric',
            ],
        ];
    }
}
