<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 *
 * @return array<string, array{
 *     path: string,
 *     type?: 'js'|'css'|'json',
 *     entrypoint?: bool,
 * }|array{
 *     version: string,
 *     package_specifier?: string,
 *     type?: 'js'|'css'|'json',
 *     entrypoint?: bool,
 * }>
 */
return [
    'app' => ['path' => './assets/app.js', 'entrypoint' => true],
    '@symfony/stimulus-bundle' => ['path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js'],
    '@symfony/ux-turbo' => ['path' => './vendor/symfony/ux-turbo/assets/dist/turbo_controller.js'],
    '@symfony/ux-live-component' => ['path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js'],
    '@symfony/ux-autocomplete' => ['path' => './vendor/symfony/ux-autocomplete/assets/dist/controller.js'],
    '@hotwired/turbo' => ['version' => '8.0.12'],
    '@hotwired/stimulus' => ['version' => '3.2.2'],
    'tom-select' => ['version' => '2.6.1'],
    '@orchidjs/sifter' => ['version' => '1.1.0'],
    '@orchidjs/unicode-variants' => ['version' => '1.1.2'],
    'tom-select/dist/css/tom-select.default.min.css' => ['version' => '2.6.1', 'type' => 'css'],
    'tom-select/dist/css/tom-select.default.css' => ['version' => '2.6.1', 'type' => 'css'],
    'tom-select/dist/css/tom-select.bootstrap4.css' => ['version' => '2.6.1', 'type' => 'css'],
    'tom-select/dist/css/tom-select.bootstrap5.css' => ['version' => '2.6.1', 'type' => 'css'],
    'react' => ['version' => '19.2.7'],
    'react-dom/client' => ['version' => '19.2.7'],
    'scheduler' => ['version' => '0.27.0'],
    'react-dom' => ['version' => '19.2.7'],
    '@symfony/ux-react' => ['path' => './vendor/symfony/ux-react/assets/dist/loader.js'],
    '@svar-ui/react-filemanager' => ['version' => '2.6.0'],
    'react/jsx-runtime' => ['version' => '19.2.7'],
    '@svar-ui/react-core' => ['version' => '2.6.0'],
    '@svar-ui/grid-locales' => ['version' => '2.7.0'],
    '@svar-ui/lib-state' => ['version' => '1.9.7'],
    '@svar-ui/filemanager-store' => ['version' => '2.6.0'],
    '@svar-ui/lib-react' => ['version' => '1.3.0'],
    '@svar-ui/lib-dom' => ['version' => '0.13.1'],
    '@svar-ui/react-menu' => ['version' => '2.6.0'],
    '@svar-ui/react-uploader' => ['version' => '2.6.0'],
    '@svar-ui/react-grid' => ['version' => '2.7.0'],
    '@svar-ui/core-locales' => ['version' => '2.6.0'],
    '@svar-ui/uploader-locales' => ['version' => '2.5.3'],
    '@svar-ui/grid-store' => ['version' => '2.7.0'],
    '@svar-ui/react-toolbar' => ['version' => '2.6.0'],
    '@svar-ui/filemanager-data-provider' => ['version' => '2.6.0'],
    'react-pdf' => ['version' => '10.4.1'],
    'pdfjs-dist' => ['version' => '5.4.296'],
    'clsx' => ['version' => '2.1.1'],
    'dequal' => ['version' => '2.0.3'],
    'make-cancellable-promise' => ['version' => '2.0.0'],
    'make-event-props' => ['version' => '2.0.0'],
    'tiny-invariant' => ['version' => '1.3.3'],
    'warning' => ['version' => '4.0.3'],
    'merge-refs' => ['version' => '2.0.0'],
];
