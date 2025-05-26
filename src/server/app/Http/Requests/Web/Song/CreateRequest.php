<?php

declare(strict_types=1);

namespace App\Http\Requests\Web\Song;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Song\Domain\Models\Archives\ArchiveType;

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
            'archives.*.archive_type' => [
                'required',
                new Enum(ArchiveType::class),
            ],
            'archives.*.archive_name' => [
                'required_if:archive_type,' . ArchiveType::YouTube->value . ',' . ArchiveType::Twitter->value,
                'string',
            ],
            'archives.*.video_url' => [
                'required_if:archive_type,' . ArchiveType::YouTube->value,
                'string',
            ],
            'archives.*.thumbnail_url' => [
                'required_if:archive_type,' . ArchiveType::YouTube->value,
                'string',
            ],
            'archives.*.post_url' => [
                'required_if:archive_type,' . ArchiveType::Twitter->value,
                'string',
            ],
            'archives.*.archived_on' => [
                'required',
                'date_format:Y-m-d',
            ],
            'archives.*.order_no' => [
                'required',
                'numeric',
            ],
        ];
    }
}
