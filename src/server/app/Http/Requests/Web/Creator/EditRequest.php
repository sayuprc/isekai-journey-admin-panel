<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Creator;

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
            'creator_id' => [
                'required',
            ],
            'creator_name' => [
                'required',
                'string',
            ],
        ];
    }
}
