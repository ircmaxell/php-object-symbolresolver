<?php

namespace PHPObjectSymbolResolver;

interface ObjectFile {
	public function is32Bit(): bool;
	public function is64Bit(): bool;

	public function hasLowestByteFirst(): bool;

	/** @return Symbol[] */
	public function getAllSymbols(): array;
}
