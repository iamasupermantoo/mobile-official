<?php

return [
    'autoload' => false,
    'hooks' => [
        'view_filter' => [
            'darktheme',
        ],
        'config_init' => [
            'darktheme',
            'summernote',
        ],
        'upgrade' => [
            'wwh',
        ],
    ],
    'route' => [
        '/$' => 'wwh/index/index',
        '/search/$' => 'wwh/index/search',
        '/[:diyname]$' => 'wwh/index/column',
        '/[:diyname]/[:id]$' => 'wwh/index/archives',
    ],
    'priority' => [],
    'domain' => '',
];
