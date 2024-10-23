<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'summary' => [
                'required',
                'string',
                'max:255',
            ],
            'story' => [
                'required',
                'string',
            ],
            'from_on' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:to_on',
            ],
            'to_on' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:from_on',
            ],
            'order_no' => [
                'required',
                'numeric',
            ],
        ];
    }
}
