<?php
namespace Psl;

return [
    'form_elements' => [
        'factories' => [
            Form\Element\MediaTypeSelect::class => Service\Form\Element\MediaTypeSelectFactory::class,
        ],
    ],
    'oaipmhrepository' => [
        'metadata_formats' => [
            'factories' => [
                'psl_dc' => Service\OaiPmh\Metadata\PslDcFactory::class,
            ],
        ],
    ],
];
