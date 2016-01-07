<?php

return [

    'db' => [
        'dsn' => 'mysql:host=test.mte-ua.com;dbname=mte_cyber',
        'username' => 'mte',
        'password' => 'EYsx5daZEjFNdSv6',
    ],

//    'db' => [
//        'dsn' => 'mysql:host=mte-ua.com;dbname=mtecyber',
//        'username' => 'ataev',
//        'password' => 'Ccp5cd7M8dZVXzhu',
//    ],

//    'db' => [
//        'dsn' => 'mysql:host=localhost;dbname=mte_cyber',
//        'username' => 'root',
//        'password' => '',
//    ],


    'ps' => [
        'address' => '127.0.0.1',
    ],

    'keys' => [
        'ours' => [
            'public' => __DIR__ . '/public.pem',
            'private' => __DIR__ . '/private.pem'
        ],
        'system' => [
            'public' => __DIR__ . '/system_public.pem'
        ]
    ],

    'log' => [
        'saveToDB'     => true,
        'saveRequest'  => true,
        'saveResponse' => true,
    ],

    'interval' => [
        'not older' => '4 hours',
        'not under' => '-1 week'
    ]

];