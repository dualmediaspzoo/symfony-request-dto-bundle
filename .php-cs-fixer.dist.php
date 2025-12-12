<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('bin')
    ->exclude('vendor');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
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
        'declare_strict_types' => true,
        'increment_style' => false,
        'ordered_class_elements' => true,
        'fully_qualified_strict_types' => false,
        'nullable_type_declaration_for_default_null_value' => false,
        'nullable_type_declaration' => ['syntax' => 'union'],
        'ordered_types' => ['sort_algorithm' => 'none', 'null_adjustment' => 'always_last'],
        'php_unit_data_provider_name' => true,
        'php_unit_data_provider_static' => true,
        'php_unit_data_provider_return_type' => true,
        'php_unit_method_casing' => ['case' => 'camel_case'],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'static'],
        'PedroTroller/line_break_between_method_arguments' => [
            'max-args' => false,
            'max-length' => 1,
            'automatic-argument-merge' => false,
            'inline-attributes' => true,
        ],
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'match',
            ],
        ],
        'attribute_empty_parentheses' => true,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
    ])
    ->setFinder($finder)
    ->registerCustomFixers(new \PedroTroller\CS\Fixer\Fixers());
