<?php

$finder = \PhpCsFixer\Finder::create()->in(['src', 'tests']);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'               => true,
        'array_syntax'           => ['syntax' => 'short'],
        'ordered_imports'        => true,
    ])
    ->setFinder($finder);