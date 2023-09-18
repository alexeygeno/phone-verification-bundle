<?php
/**
 * @see https://cs.symfony.com/doc/rules/index.html
 */

$finder = PhpCsFixer\Finder::create()
            ->in(['src', 'tests'])
            ->exclude(['TestApplication/var']);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'               => true,
        'array_syntax'           => ['syntax' => 'short'],
        'ordered_imports'        => true,
    ])
    ->setFinder($finder);