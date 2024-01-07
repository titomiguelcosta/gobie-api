<?php

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
;
