<?php
/**
 * @see https://cs.symfony.com/doc/rules/index.html
 */
return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'               => true,
        'array_syntax'           => ['syntax' => 'short'],
        'ordered_imports'        => true,
    ])
    ->setFinder(PhpCsFixer\Finder::create()->in(['src', 'tests']));