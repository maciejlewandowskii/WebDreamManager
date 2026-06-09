<?php

return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@symfony/ux-turbo' => [
        'path' => './vendor/symfony/ux-turbo/assets/dist/turbo_controller.js',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    '@symfony/ux-autocomplete' => [
        'path' => './vendor/symfony/ux-autocomplete/assets/dist/controller.js',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.12',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
];
