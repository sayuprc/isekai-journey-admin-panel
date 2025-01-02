<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Adapter\Web\Requests;

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
            'journey_log_link_type_id' => [
                'required',
                'string',
            ],
            'journey_log_link_type_name' => [
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
