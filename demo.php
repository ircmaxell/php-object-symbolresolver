<?php

require __DIR__ . '/vendor/autoload.php';

$file = '/usr/lib/x86_64-linux-gnu/libLLVM-9.so.1';

$parser = new PHPELFSymbolResolver\Parser;

$objFile = $parser->parse($file);

var_dump($objFile->getAllSymbols());