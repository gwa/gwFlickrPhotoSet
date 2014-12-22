<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->notName('README.md')
    ->notName('composer.*')
    ->exclude('vendor')
    ->exclude('.scrutinizer.yml')
    ->exclude('.travis.yml')
    ->exclude('.php_cs')
    ->exclude('tests')
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    // use default SYMFONY_LEVEL and extra fixers:
    ->finder($finder);
