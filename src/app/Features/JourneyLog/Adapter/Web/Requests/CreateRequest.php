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
            'journey_log_links' => [
                'nullable',
                'array',
            ],
            'journey_log_links.*.journey_log_link_name' => [
                'required',
                'string',
            ],
            'journey_log_links.*.url' => [
                'required',
                'string',
            ],
            'journey_log_links.*.order_no' => [
                'required',
                'numeric',
            ],
            'journey_log_links.*.journey_log_link_type_id' => [
                'required',
                'string',
            ],
        ];
    }
}
