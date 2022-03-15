<?php

namespace PHPObjectSymbolResolver\MachO;

class SymtabCommand {
	public int $symOff;
	public int $nSyms;
	public int $strOff;
	public int $strSize;

	/** @var Symbol[] */
	public array $symbols = [];
}
