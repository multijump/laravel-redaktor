<?php

return [
    '@PSR2' => true,

    // PSR12 Rules
    'blank_line_after_opening_tag' => true,
    'braces' => ['allow_single_line_closure' => true],
    'compact_nullable_typehint' => true,
    'concat_space' => ['spacing' => 'one'],
    'declare_equal_normalize' => ['space' => 'none'],
    'function_typehint_space' => true,
    'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
    'new_with_braces' => true,
    'no_empty_statement' => true,
    'no_leading_import_slash' => true,
    'no_leading_namespace_whitespace' => true,
    'no_whitespace_in_blank_line' => true,
    'return_type_declaration' => ['space_before' => 'none'],
    'single_trait_insert_per_statement' => true,
    // End of PSR12 Rules

    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => true,
    'strict_param' => true,
    'trailing_comma_in_multiline_array' => true,
    'visibility_required' => [
        'elements' => [
            'property', 'method', 'const',
        ],
    ],
    'whitespace_after_comma_in_array' => true,
];
