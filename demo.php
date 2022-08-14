<?php

require __DIR__ . '/vendor/autoload.php';

$file = '/usr/lib/x86_64-linux-gnu/libLLVM-9.so.1';

$objFile = \PHPObjectSymbolResolver\Parser::parseFor($file);

var_dump($objFile->getAllSymbols());