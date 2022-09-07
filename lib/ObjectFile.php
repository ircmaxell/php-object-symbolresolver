<?php

namespace PHPObjectSymbolResolver;

interface ObjectFile {
	public function is32Bit(): bool;
	public function is64Bit(): bool;

	public function hasLowestByteFirst(): bool;

    public function resolveDependentObjectsRecursively();

    /** @return string[] */
	public function getAllSymbols(): array;

	/** @return string[] */
	public function getAllSymbolsRecursively(): array;
}
