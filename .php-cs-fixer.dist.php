<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/com_swjprojects',
        __DIR__ . '/mod_swjprojects_categories',
        __DIR__ . '/mod_swjprojects_projects',
        __DIR__ . '/mod_swjprojects_versions',
        __DIR__ . '/plg_actionlog_swjprojects',
        __DIR__ . '/plg_content_swjprojects',
        __DIR__ . '/plg_editors-xtd_swjprojectseditorxtd',
        __DIR__ . '/plg_finder_swjprojects_documentation',
        __DIR__ . '/plg_finder_swjprojects_projects',
        __DIR__ . '/plg_schemaorg_softwareapplicationswjprojects',
        __DIR__ . '/plg_swjprojects_joomlaserverscheme',
        __DIR__ . '/plg_webservices_swjprojects',
    ])
    ->name('*.php')
    ->notPath('vendor')
    ->notPath('tmp')
    ->notName('*.min.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setIndent("\t")
    ->setLineEnding("\r\n")
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['operators' => ['=>' => 'align_single_space_minimal']],
        'no_unused_imports' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
    ])
    ->setFinder($finder);
