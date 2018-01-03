<?php

return [
    'credentials' => [
        'key'    => getenv('AWS_KEY'),
        'secret' => getenv('AWS_SECRET'),
    ],
    'region' => getenv('AWS_REGION'),
    'version' => 'latest',
];