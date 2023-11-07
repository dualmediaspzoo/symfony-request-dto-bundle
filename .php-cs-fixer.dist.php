<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('assets')
    ->exclude('bin')
    ->exclude('config')
    ->exclude('docker')
    ->exclude('files')
    ->exclude('public')
    ->exclude('scripts')
    ->exclude('templates')
    ->exclude('tools')
    ->exclude('translations')
    ->exclude('var')
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'declare',
                'return',
                'throw',
                'try',
                'if',
                'for',
                'while',
                'foreach',
            ],
        ],
        'cast_spaces' => ['space' => 'none'],
        'constant_case' => ['case' => 'lower'],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'phpdoc_align' => false,
        'single_line_throw' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_types_order' => false,
        'phpdoc_scalar' => false,
        'phpdoc_types' => false,
        'increment_style' => false,
        'ordered_class_elements' => true,
        'nullable_type_declaration_for_default_null_value' => false,
        'nullable_type_declaration' => ['syntax' => 'union'],
        'ordered_types' => ['sort_algorithm' => 'none', 'null_adjustment' => 'always_last'],
        'PedroTroller/line_break_between_method_arguments' => [
            'max-args' => false,
            'max-length' => 1,
            'automatic-argument-merge' => false,
            'inline-attributes' => true,
        ],
    ])
    ->setFinder($finder)
    ->registerCustomFixers(new \PedroTroller\CS\Fixer\Fixers());
