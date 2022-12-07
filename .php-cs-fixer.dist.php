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
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_superfluous_phpdoc_tags' => false,
        'ternary_to_null_coalescing' => true,
        'no_useless_else' => true,
        'ordered_class_elements' => true,
        'elseif' => true,
        'no_useless_return' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'single_line_throw' => false,
        'yoda_style' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'array_indentation' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_line_after_imports' => true,
        'single_import_per_statement' => true,
        'blank_line_after_opening_tag' => true,
        'compact_nullable_typehint' => true,
        'clean_namespace' => true,
        'binary_operator_spaces' => true,
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
        'full_opening_tag' => true,
        'indentation_type' => true,
        'types_spaces' => ['space' => 'none'],
        'lowercase_cast' => true,
        'native_function_casing' => true,
        'no_alternative_syntax' => true,
        'new_with_braces' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_closing_tag' => true,
        'no_short_bool_cast' => true,
        'no_trailing_whitespace' => true,
        'no_unset_cast' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_indent' => true,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_trim' => true,
        'single_blank_line_at_eof' => true,
        'short_scalar_cast' => true,
        'single_class_element_per_statement' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'visibility_required' => true,
        'PedroTroller/line_break_between_method_arguments' => ['max-args' => false, 'max-length' => 1, 'automatic-argument-merge' => false],
    ])
    ->setFinder($finder)
    ->registerCustomFixers(new PedroTroller\CS\Fixer\Fixers())
    ;
