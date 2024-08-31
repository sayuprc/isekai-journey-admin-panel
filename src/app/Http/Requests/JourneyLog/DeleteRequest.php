<?php

declare(strict_types=1);

namespace App\Http\Requests\JourneyLog;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends FormRequest
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
        ];
    }
}
