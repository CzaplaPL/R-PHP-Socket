<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__.DIRECTORY_SEPARATOR.'./src')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR2' => true,
        'ereg_to_preg' => true,
        'self_accessor' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'date_time_immutable' => true,
        'fully_qualified_strict_types' => true,
        'no_useless_else' => true,
        'ternary_to_null_coalescing' => true,
        'void_return' => true,
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => false,
        'return_type_declaration' => [
            'space_before' => 'none'
        ],
        'phpdoc_to_comment' => false,
        'phpdoc_align' => false,
        'align_multiline_comment' => false
    ])
    ->setFinder($finder)
    ;
