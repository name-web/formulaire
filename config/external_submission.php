<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External Submission Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for sending submissions to the external processing system.
    | The external system receives pending submissions and decides whether
    | to push them back for final import into Dominium.
    |
    */

    'url' => env('EXTERNAL_SUBMISSION_URL', ''),

    'token' => env('EXTERNAL_SUBMISSION_TOKEN', ''),

    'timeout' => (int) env('EXTERNAL_SUBMISSION_TIMEOUT', 30),

    'connect_timeout' => (int) env('EXTERNAL_SUBMISSION_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | File Storage
    |--------------------------------------------------------------------------
    |
    | Configuration for temporary file storage before sending.
    | Files are stored locally and sent as multipart to the external system.
    |
    */

    'file_disk' => env('EXTERNAL_SUBMISSION_FILE_DISK', 'local'),

];
