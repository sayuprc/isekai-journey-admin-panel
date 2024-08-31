<?php

declare(strict_types=1);

namespace App\Http\Requests\JourneyLog;

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
            'journey_log_id' => [
                'required',
            ],
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
