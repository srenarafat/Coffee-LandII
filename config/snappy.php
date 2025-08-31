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
            'disable-smart-shrinking' => false,
            'margin-top' => '5mm',
            'margin-right' => '5mm',
            'margin-bottom' => '5mm',
            'margin-left' => '5mm',
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
