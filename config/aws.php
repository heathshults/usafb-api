<?php

return [
    'credentials' => [
        'key'    => getenv('AWS_KEY'),
        'secret' => getenv('AWS_SECRET'),
    ],
    'region' => 'us-east-1',
    'version' => 'latest',
];
