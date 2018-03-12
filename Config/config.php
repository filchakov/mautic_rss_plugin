<?php

return [
    'name'        => 'Jobs RSS',
    'description' => 'Enables Jobs RSS feed in mail body',
    'version'     => '1.0',
    'author'      => 'Filchakov Denis',
    'services' => [
        'events' => [
            'mautic.plugin.jobsrsstoemail.subscriber' => [
                'class'     => 'MauticPlugin\MauticJobsRssExtensionBundle\EventListener\EmailSubscriber',
            ],
        ],
    ],
];