<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src')
    ->name('*.php')
    ->notPath('Kernel.php');

return new Config()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                             => true,
        '@Symfony:risky'                       => true,
        '@PHP84Migration'                      => true,
        '@PHP84Migration:risky'                => true,
        'declare_strict_types'                 => true,
        'strict_param'                         => true,
        'array_syntax'                         => ['syntax' => 'short'],
        'ordered_imports'                      => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                    => true,
        'single_quote'                         => true,
        'trailing_comma_in_multiline'          => true,
        'yoda_style'                           => false,
        'concat_space'                         => ['spacing' => 'one'],
        'binary_operator_spaces'               => ['default' => 'align_single_space_minimal'],
        'method_argument_space'                => ['on_multiline' => 'ensure_fully_multiline'],
        'fully_qualified_strict_types'         => true,
        'global_namespace_import'              => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
    ])
    ->setFinder($finder);
