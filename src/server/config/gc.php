<?php

declare(strict_types=1);

return [
    'google_cloud_project' => env('GOOGLE_CLOUD_PROJECT'),

    'pubsub' => [
        'notification' => [
            'topic' => env('NOTIFICATION_TOPIC'),
        ],
    ],
];
