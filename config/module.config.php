<?php
namespace Psl;

return [
    'oaipmhrepository' => [
        'metadata_formats' => [
            'factories' => [
                'psl_dc' => Service\OaiPmh\Metadata\PslDcFactory::class,
            ],
        ],
    ],
];
