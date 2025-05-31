<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
  ->in(__DIR__)
  ->exclude('vendor');

return (new Config())
  ->setRules([
    '@PSR12' => true,
    'binary_operator_spaces' => [
      'operators' => ['=>' => 'align_single_space_minimal']
    ],
    'array_syntax' => ['syntax' => 'short'],
    'no_unused_imports' => true,
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'blank_line_after_namespace' => true,
  ])
  ->setFinder($finder);
