<?php

return [

    'ssr' => [
        'enabled' => true,
        'url' => 'http://127.0.0.1:13714',

    ],

    'auth' => [

        'paths' => [
            resource_path('js/auth'),
        ],

        'extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],

    ],

    'testing' => [

        'ensure_pages_exist' => true,

    ],

];
