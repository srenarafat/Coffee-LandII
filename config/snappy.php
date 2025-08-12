<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary' => '"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe"',
        'timeout' => false,
        'options' => [
            'encoding' => 'UTF-8',
            'no-outline' => null,
            'enable-local-file-access' => true,
            'disable-smart-shrinking' => true,
            'margin-top' => 0,
            'margin-right' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
        ],
        'env' => [],
    ],

    'image' => [
        'enabled' => true,
        'binary'  => env('WKHTML_IMG_BINARY', '"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltoimage.exe"'),
        'timeout' => false,
        'options' => [],
        'env'     => [],
    ],

];
